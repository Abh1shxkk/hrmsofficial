<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Holiday;
use App\Models\SalarySlip;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    private const PF_RATE = 0.12;
    private const ESI_EMPLOYEE_RATE = 0.0075;
    private const ESI_EMPLOYER_RATE = 0.0325;
    private const ESI_GROSS_LIMIT = 21000;

    public function generate(Employee $employee, int $month, int $year): SalarySlip
    {
        if (SalarySlip::where('employee_id', $employee->id)->where('month', $month)->where('year', $year)->exists()) {
            throw new \Exception('Salary slip already generated for this employee and month.');
        }

        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();
        $structure = $employee->salaryStructures()
            ->whereDate('effective_from', '<=', $periodEnd)
            ->latest('effective_from')
            ->firstOrFail();

        $workingDays = $this->getWorkingDays($month, $year);

        $monthAttendances = $employee->attendances()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->whereIn('status', ['present', 'wfh', 'half_day'])
            ->get();

        $presentDays = $monthAttendances->reduce(function ($carry, $a) {
            return $carry + ($a->status === 'half_day' ? 0.5 : 1);
        }, 0);

        // Pro-rate ratio: present days / working days (cap at 1.0)
        $ratio = $workingDays > 0 ? min($presentDays / $workingDays, 1.0) : 1.0;

        $calculation = $this->calculate(
            (float) $structure->basic,
            (float) $structure->hra,
            (float) ($structure->transport_allowance ?? 0),
            (float) ($structure->other_allowances ?? 0),
            $ratio
        );

        return DB::transaction(fn() => SalarySlip::create([
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
            'basic' => $calculation['basic'],
            'hra' => $calculation['hra'],
            'transport_allowance' => $calculation['transport_allowance'],
            'other_allowances' => $calculation['other_allowances'],
            'gross_salary' => $calculation['gross_salary'],
            'pf_employee' => $calculation['pf_employee'],
            'pf_employer' => $calculation['pf_employer'],
            'esi_employee' => $calculation['esi_employee'],
            'esi_employer' => $calculation['esi_employer'],
            'tds' => $calculation['tds'],
            'total_deductions' => $calculation['total_deductions'],
            'net_salary' => $calculation['net_salary'],
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'status' => 'processed',
            'generated_by' => auth()->id(),
        ]));
    }

    /**
     * Calculate salary components.
     * $ratio = present_days / working_days (1.0 = full month, used for pro-rating)
     */
    public function calculate(float $basic, float $hra, float $transportAllowance = 0, float $otherAllowances = 0, float $ratio = 1.0): array
    {
        // Pro-rate each component by attendance ratio
        $basic = round($basic * $ratio, 2);
        $hra = round($hra * $ratio, 2);
        $transportAllowance = round($transportAllowance * $ratio, 2);
        $otherAllowances = round($otherAllowances * $ratio, 2);

        $gross = round($basic + $hra + $transportAllowance + $otherAllowances, 2);
        $pfEmployee = round($basic * self::PF_RATE, 2);
        $pfEmployer = round($basic * self::PF_RATE, 2);

        $esiEmployee = 0.0;
        $esiEmployer = 0.0;
        if ($gross <= self::ESI_GROSS_LIMIT) {
            $esiEmployee = round($gross * self::ESI_EMPLOYEE_RATE, 2);
            $esiEmployer = round($gross * self::ESI_EMPLOYER_RATE, 2);
        }

        $tds = $this->calculateMonthlyTDS($gross * 12);
        $totalDeductions = round($pfEmployee + $esiEmployee + $tds, 2);
        $netSalary = round($gross - $totalDeductions, 2);

        return [
            'basic' => $basic,
            'hra' => $hra,
            'transport_allowance' => $transportAllowance,
            'other_allowances' => $otherAllowances,
            'gross_salary' => $gross,
            'pf_employee' => $pfEmployee,
            'pf_employer' => $pfEmployer,
            'esi_employee' => $esiEmployee,
            'esi_employer' => $esiEmployer,
            'tds' => $tds,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
        ];
    }

    private function calculateMonthlyTDS(float $annualGross): float
    {
        if ($annualGross <= 300000) {
            $annualTax = 0;
        } elseif ($annualGross <= 700000) {
            $annualTax = ($annualGross - 300000) * 0.05;
        } elseif ($annualGross <= 1000000) {
            $annualTax = 20000 + ($annualGross - 700000) * 0.10;
        } elseif ($annualGross <= 1200000) {
            $annualTax = 50000 + ($annualGross - 1000000) * 0.15;
        } elseif ($annualGross <= 1500000) {
            $annualTax = 80000 + ($annualGross - 1200000) * 0.20;
        } else {
            $annualTax = 140000 + ($annualGross - 1500000) * 0.30;
        }

        return round($annualTax / 12, 2);
    }

    private function getWorkingDays(int $month, int $year): int
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $holidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('date')
            ->map(fn($d) => $d->toDateString())
            ->toArray();

        $days = 0;
        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (! $date->isWeekend() && ! in_array($date->toDateString(), $holidays, true)) {
                $days++;
            }
        }

        return $days;
    }
}
