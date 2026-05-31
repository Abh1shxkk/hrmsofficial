<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public function apply(Employee $employee, array $data): LeaveApplication
    {
        $fromDate = Carbon::parse($data['from_date']);
        $toDate = Carbon::parse($data['to_date']);
        $year = $fromDate->year;
        $totalDays = $this->calculateWorkingDays($fromDate->toDateString(), $toDate->toDateString());

        if ($totalDays <= 0) {
            throw new \Exception('Selected leave range has no working days.');
        }

        $this->ensureBalancesForEmployee($employee, $year);

        return DB::transaction(function () use ($employee, $data, $fromDate, $toDate, $totalDays, $year) {
            $hasOverlap = LeaveApplication::where('employee_id', $employee->id)
                ->whereIn('status', ['pending', 'approved'])
                ->whereDate('from_date', '<=', $toDate)
                ->whereDate('to_date', '>=', $fromDate)
                ->lockForUpdate()
                ->exists();

            if ($hasOverlap) {
                throw new \Exception('A pending or approved leave already exists for the selected dates.');
            }

            $balance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $data['leave_type_id'])
                ->where('year', $year)
                ->lockForUpdate()
                ->firstOrFail();

            if ($balance->balance < $totalDays) {
                throw new \Exception("Insufficient leave balance. Available: {$balance->balance}, Requested: {$totalDays}");
            }

            $balance->pending += $totalDays;
            $balance->save();

            return LeaveApplication::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $data['leave_type_id'],
                'from_date' => $fromDate->toDateString(),
                'to_date' => $toDate->toDateString(),
                'total_days' => $totalDays,
                'reason' => $data['reason'],
                'status' => 'pending',
            ]);
        });
    }

    public function approve(LeaveApplication $application, int $approverId): void
    {
        DB::transaction(function () use ($application, $approverId) {
            $balance = LeaveBalance::where('employee_id', $application->employee_id)
                ->where('leave_type_id', $application->leave_type_id)
                ->where('year', $application->from_date->year)
                ->lockForUpdate()
                ->firstOrFail();

            $balance->pending = max(0, $balance->pending - $application->total_days);
            $balance->used += $application->total_days;
            $balance->save();

            $application->update([
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);
        });
    }

    public function reject(LeaveApplication $application, string $reason, ?int $approverId = null): void
    {
        DB::transaction(function () use ($application, $reason, $approverId) {
            $balance = LeaveBalance::where('employee_id', $application->employee_id)
                ->where('leave_type_id', $application->leave_type_id)
                ->where('year', $application->from_date->year)
                ->lockForUpdate()
                ->firstOrFail();

            $balance->pending = max(0, $balance->pending - $application->total_days);
            $balance->save();

            $application->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);
        });
    }

    public function ensureBalancesForAll(?int $year = null): void
    {
        $year ??= now()->year;
        $leaveTypes = LeaveType::orderBy('id')->get();

        Employee::active()->select('id')->chunkById(100, function ($employees) use ($year, $leaveTypes) {
            foreach ($employees as $employee) {
                $this->ensureBalancesForEmployee($employee, $year, $leaveTypes);
            }
        });
    }

    public function ensureBalancesForMissing(?int $year = null): void
    {
        $year ??= now()->year;
        $leaveTypes = LeaveType::orderBy('id')->get();

        // Only process employees who are missing at least one balance for this year
        $employeesWithFullBalances = LeaveBalance::where('year', $year)
            ->select('employee_id')
            ->groupBy('employee_id')
            ->havingRaw('COUNT(*) >= ?', [$leaveTypes->count()])
            ->pluck('employee_id');

        Employee::active()
            ->whereNotIn('id', $employeesWithFullBalances)
            ->select('id')
            ->chunkById(100, function ($employees) use ($year, $leaveTypes) {
                foreach ($employees as $employee) {
                    $this->ensureBalancesForEmployee($employee, $year, $leaveTypes);
                }
            });
    }

    public function ensureBalancesForEmployee(Employee $employee, ?int $year = null, $leaveTypes = null): void
    {
        $year ??= now()->year;
        $leaveTypes ??= LeaveType::orderBy('id')->get();

        foreach ($leaveTypes as $leaveType) {
            $previousBalance = 0;

            if ($leaveType->is_carry_forward) {
                $previousBalance = (float) LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('year', $year - 1)
                    ->value('balance');

                // Cap EL carry-forward at 30 days
                $previousBalance = min($previousBalance, 30);
            }

            LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                ],
                [
                    'allocated' => $leaveType->max_days_per_year,
                    'carried_forward' => max(0, $previousBalance),
                    'used' => 0,
                    'pending' => 0,
                    'balance' => 0,
                ]
            );
        }
    }

    public function calculateWorkingDays(string $from, string $to): float
    {
        $holidays = Holiday::pluck('date')->map(fn($d) => $d->toDateString())->toArray();
        $period = CarbonPeriod::create($from, $to);
        $days = 0;

        foreach ($period as $date) {
            if (!$date->isWeekend() && !in_array($date->toDateString(), $holidays)) {
                $days++;
            }
        }

        return $days;
    }
}
