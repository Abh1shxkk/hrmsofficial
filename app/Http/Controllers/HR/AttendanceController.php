<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use League\Csv\Writer;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = $user->role;

        if (in_array($role, ['employee', 'manager']) && ! $user->employee) {
            return $this->missingEmployeeProfileResponse();
        }

        if (in_array($role, ['super_admin', 'hr_admin'])) {
            $query = Attendance::with(['employee.user']);
        } elseif ($role === 'manager' && $user->employee) {
            $teamIds = Employee::where('department_id', $user->employee->department_id)->pluck('id');
            $query = Attendance::with(['employee.user'])->whereIn('employee_id', $teamIds);
        } else {
            $query = Attendance::with(['employee.user'])->where('employee_id', $user->employee?->id);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($date = $request->get('date')) {
            $query->whereDate('date', $date);
        }
        if ($search = $request->get('search')) {
            $query->whereHas('employee.user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $attendances = $query->latest('date')->paginate(15)->withQueryString();
        $employees = in_array($role, ['super_admin', 'hr_admin']) ? Employee::active()->with('user')->get() : collect();

        return view('attendance.index', compact('attendances', 'employees'));
    }

    public function mark(Request $request)
    {
        $user = auth()->user();
        $role = $user->role;

        if (in_array($role, ['employee', 'manager']) && ! $user->employee) {
            return $this->missingEmployeeProfileResponse();
        }

        $dateRules = 'required|date|before_or_equal:today';

        // Only HR/super_admin can backdate attendance
        if (! in_array($role, ['super_admin', 'hr_admin'])) {
            $dateRules = 'required|date|date_equals:today';
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => $dateRules,
            'status' => 'required|in:present,absent,half_day,wfh',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'remarks' => 'nullable|string',
        ]);

        // Employee can only mark own attendance
        if ($role === 'employee' && $request->employee_id != $user->employee?->id) {
            abort(403);
        }

        // Manager can only mark for employees in their department
        if ($role === 'manager') {
            $targetEmployee = Employee::find($request->employee_id);
            abort_unless(
                $user->employee && $targetEmployee && $targetEmployee->department_id === $user->employee->department_id,
                403
            );
        }

        if ($holiday = Holiday::whereDate('date', $request->date)->first()) {
            return back()
                ->with('error', "Attendance cannot be marked on holiday: {$holiday->name}.")
                ->withInput();
        }

        Attendance::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            [
                'status' => $request->status,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'remarks' => $request->remarks,
                'marked_by' => $user->id,
            ]
        );

        return redirect()->route('attendance.index')->with('success', 'Attendance marked.');
    }

    public function report(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $departmentId = $request->get('department_id');
        $employeeId = $request->get('employee_id');

        $user = auth()->user();
        $role = $user->role;

        if (in_array($role, ['employee', 'manager']) && ! $user->employee) {
            return $this->missingEmployeeProfileResponse();
        }

        $query = $this->attendanceReportQuery($request);

        $attendances = $query->orderBy('date')->get();
        $employees = $this->visibleEmployees($user)->with(['user', 'department'])->orderBy('employee_code')->get();
        $departments = in_array($role, ['super_admin', 'hr_admin'])
            ? Department::orderBy('name')->get()
            : Department::where('id', $user->employee?->department_id)->get();

        $employeeSummary = $this->employeeMonthlySummary($attendances);
        $departmentSummary = $this->departmentSummary($attendances);
        $totals = $this->statusTotals($attendances);

        return view('attendance.report', compact(
            'attendances',
            'month',
            'year',
            'departmentId',
            'employeeId',
            'employees',
            'departments',
            'employeeSummary',
            'departmentSummary',
            'totals'
        ));
    }

    public function export(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $format = $request->get('format', 'csv');

        if (in_array(auth()->user()->role, ['employee', 'manager']) && ! auth()->user()->employee) {
            return $this->missingEmployeeProfileResponse();
        }

        $attendances = $this->attendanceReportQuery($request)
            ->orderBy('date')
            ->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('attendance.report-pdf', [
                'attendances' => $attendances,
                'employeeSummary' => $this->employeeMonthlySummary($attendances),
                'departmentSummary' => $this->departmentSummary($attendances),
                'totals' => $this->statusTotals($attendances),
                'month' => $month,
                'year' => $year,
            ])->setPaper('a4', 'landscape');

            return $pdf->download("attendance-{$month}-{$year}.pdf");
        }

        $csv = Writer::createFromString('');
        $csv->insertOne(['Employee Code', 'Name', 'Department', 'Date', 'Status', 'Check In', 'Check Out', 'Remarks']);

        foreach ($attendances as $a) {
            $csv->insertOne([
                $a->employee->employee_code,
                $a->employee->user->name,
                $a->employee->department->name ?? '-',
                $a->date->format('Y-m-d'),
                $a->status,
                $a->check_in,
                $a->check_out,
                $a->remarks,
            ]);
        }

        return response($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=attendance-{$month}-{$year}.csv",
        ]);
    }

    private function attendanceReportQuery(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $query = Attendance::with(['employee.user', 'employee.department'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year);

        $visibleEmployeeIds = $this->visibleEmployees($user)->pluck('id');
        $query->whereIn('employee_id', $visibleEmployeeIds);

        if ($departmentId = $request->get('department_id')) {
            $query->whereHas('employee', fn ($employee) => $employee->where('department_id', $departmentId));
        }

        if ($employeeId = $request->get('employee_id')) {
            $query->where('employee_id', $employeeId);
        }

        return $query;
    }

    private function visibleEmployees($user)
    {
        $query = Employee::query();

        if ($user->hasRole('employee')) {
            return $query->where('id', $user->employee?->id);
        }

        if ($user->hasRole('manager') && $user->employee) {
            return $query->where('department_id', $user->employee->department_id);
        }

        return $query;
    }

    private function employeeMonthlySummary($attendances)
    {
        return $attendances
            ->groupBy('employee_id')
            ->map(function ($records) {
                $employee = $records->first()->employee;
                $halfDays = $records->where('status', 'half_day')->count();

                return [
                    'employee' => $employee,
                    'present' => $records->where('status', 'present')->count(),
                    'absent' => $records->where('status', 'absent')->count(),
                    'half_day' => $halfDays,
                    'wfh' => $records->where('status', 'wfh')->count(),
                    'total' => $records->count(),
                    'effective_present' => $records->where('status', 'present')->count()
                        + $records->where('status', 'wfh')->count()
                        + ($halfDays * 0.5),
                ];
            })
            ->sortBy(fn ($row) => $row['employee']->employee_code)
            ->values();
    }

    private function departmentSummary($attendances)
    {
        return $attendances
            ->groupBy(fn ($record) => $record->employee->department_id)
            ->map(function ($records) {
                $department = $records->first()->employee->department;
                $halfDays = $records->where('status', 'half_day')->count();

                return [
                    'department' => $department,
                    'present' => $records->where('status', 'present')->count(),
                    'absent' => $records->where('status', 'absent')->count(),
                    'half_day' => $halfDays,
                    'wfh' => $records->where('status', 'wfh')->count(),
                    'total' => $records->count(),
                    'effective_present' => $records->where('status', 'present')->count()
                        + $records->where('status', 'wfh')->count()
                        + ($halfDays * 0.5),
                ];
            })
            ->sortBy(fn ($row) => $row['department']->name ?? '')
            ->values();
    }

    private function statusTotals($attendances): array
    {
        $halfDays = $attendances->where('status', 'half_day')->count();

        return [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'half_day' => $halfDays,
            'wfh' => $attendances->where('status', 'wfh')->count(),
            'total' => $attendances->count(),
            'effective_present' => $attendances->where('status', 'present')->count()
                + $attendances->where('status', 'wfh')->count()
                + ($halfDays * 0.5),
        ];
    }

    private function missingEmployeeProfileResponse()
    {
        return redirect()
            ->route('dashboard')
            ->with('error', 'Your employee profile is not created yet. Please contact HR or Super Admin to complete your profile setup.');
    }
}
