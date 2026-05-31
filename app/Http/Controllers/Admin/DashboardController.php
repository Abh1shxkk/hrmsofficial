<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\SalarySlip;
use App\Models\Task;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role ?? 'employee';
        $data = [];

        if ($role === 'super_admin') {
            $data['total_users'] = User::count();
            $data['total_employees'] = Employee::active()->count();
            $data['total_departments'] = Department::count();
            $data['monthly_payroll'] = SalarySlip::currentMonth()->sum('net_salary');
            $data['pending_leaves'] = LeaveApplication::pending()->count();
            $data['inactive_users'] = User::where('is_active', false)->count();
            $data['open_tasks'] = Task::pending()->count();
            $data['recent_users'] = User::latest()->take(5)->get();
            $data['recent_slips'] = SalarySlip::with('employee.user')->latest()->take(5)->get();
        }

        if (in_array($role, ['hr_admin', 'super_admin'])) {
            $data['pending_leaves'] = LeaveApplication::pending()->count();
            $data['today_present'] = Attendance::today()->present()->count();
            $data['today_absent'] = Attendance::today()->absent()->count();
            $data['active_employees'] = Employee::active()->count();
            $data['employees_without_salary'] = Employee::active()
                ->whereDoesntHave('salaryStructures', fn ($query) => $query->where('is_active', true))
                ->count();
            $data['recent_leaves'] = LeaveApplication::with(['employee.user', 'leaveType'])->latest()->take(5)->get();
        }

        if ($role === 'manager') {
            $emp = $user->employee;
            if ($emp) {
                $deptId = $emp->department_id;
                $data['my_team'] = Employee::with('user')->inDepartment($deptId)->get();
                $data['pending_approvals'] = LeaveApplication::forTeam($deptId)->pending()->count();
                $data['pending_tasks'] = Task::where('assigned_by', $user->id)->pending()->count();
                $data['completed_tasks'] = Task::where('assigned_by', $user->id)->where('status', 'completed')->count();
                $data['team_present_today'] = Attendance::today()
                    ->whereIn('employee_id', $data['my_team']->pluck('id'))
                    ->present()
                    ->count();
                $data['team_leaves'] = LeaveApplication::with(['employee.user', 'leaveType'])
                    ->forTeam($deptId)
                    ->latest()
                    ->take(5)
                    ->get();
                $data['assigned_tasks'] = Task::with('assignedEmployee.user')
                    ->where('assigned_by', $user->id)
                    ->latest()
                    ->take(5)
                    ->get();
            }
        }

        if ($role === 'employee') {
            $emp = $user->employee;
            if ($emp) {
                $data['leave_balances'] = LeaveBalance::where('employee_id', $emp->id)
                    ->where('year', now()->year)->with('leaveType')->get();
                $data['my_tasks'] = Task::where('assigned_to', $emp->id)->latest()->take(5)->get();
                $data['my_attendance'] = Attendance::where('employee_id', $emp->id)
                    ->whereMonth('date', now()->month)->get();
                $data['latest_slip'] = SalarySlip::where('employee_id', $emp->id)->latest()->first();
                $data['salary_slips_count'] = SalarySlip::where('employee_id', $emp->id)->count();
                $data['pending_tasks'] = Task::where('assigned_to', $emp->id)->pending()->count();
                $data['completed_tasks'] = Task::where('assigned_to', $emp->id)->where('status', 'completed')->count();
                $data['attendance_present'] = $data['my_attendance']->whereIn('status', ['present', 'wfh'])->count();
                $data['attendance_absent'] = $data['my_attendance']->where('status', 'absent')->count();
                $data['recent_leaves'] = LeaveApplication::with('leaveType')
                    ->where('employee_id', $emp->id)
                    ->latest()
                    ->take(5)
                    ->get();
            }
        }

        return view('dashboard.index', compact('data', 'role'));
    }
}
