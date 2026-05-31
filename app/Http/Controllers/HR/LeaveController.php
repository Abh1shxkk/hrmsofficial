<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Services\LeaveService;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(protected LeaveService $leaveService) {}

    public function balance()
    {
        $user = auth()->user();
        $role = $user->role;
        $year = now()->year;

        if (in_array($role, ['super_admin', 'hr_admin'])) {
            // Ensure balances only for active employees who don't have them yet
            $this->leaveService->ensureBalancesForMissing($year);

            $balances = LeaveBalance::with(['employee.user', 'leaveType'])
                ->whereHas('employee', fn($q) => $q->where('status', 'active'))
                ->where('year', $year)->get()->groupBy('employee_id');
        } else {
            if (! $user->employee) {
                return $this->missingEmployeeProfileResponse();
            }

            $empId = $user->employee?->id;
            if ($empId) {
                $this->leaveService->ensureBalancesForEmployee($user->employee, $year);
            }
            $balances = $empId
                ? LeaveBalance::with(['employee.user', 'leaveType'])
                    ->where('employee_id', $empId)->where('year', $year)->get()->groupBy('employee_id')
                : collect();
        }

        return view('leaves.balance', compact('balances'));
    }

    public function approvals(Request $request)
    {
        $user = auth()->user();
        $query = LeaveApplication::with(['employee.user', 'employee.department', 'leaveType']);
        $isManagerOnly = $user->hasRole('manager') && ! $user->hasAnyRole(['super_admin', 'hr_admin']);

        if ($isManagerOnly) {
            $departmentId = $user->employee?->department_id;
            abort_if(! $departmentId, 403);
            $query->forTeam($departmentId);
        }

        $statusFilter = $request->get('status', 'pending');
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }
        if ($search = $request->get('search')) {
            $query->whereHas('employee.user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $applications = $query->latest()->paginate(15)->withQueryString();

        return view('leaves.approvals', compact('applications', 'statusFilter'));
    }

    public function approve(LeaveApplication $leaveApplication)
    {
        $this->authorizeApproval($leaveApplication);

        if ($leaveApplication->status !== 'pending') {
            return back()->with('error', 'This leave application has already been processed.');
        }

        $this->leaveService->approve($leaveApplication, auth()->id());

        return back()->with('success', 'Leave approved successfully.');
    }

    public function reject(Request $request, LeaveApplication $leaveApplication)
    {
        $request->validate(['rejection_reason' => 'required|string|min:5|max:500']);
        $this->authorizeApproval($leaveApplication);

        if ($leaveApplication->status !== 'pending') {
            return back()->with('error', 'This leave application has already been processed.');
        }

        $this->leaveService->reject($leaveApplication, $request->rejection_reason, auth()->id());

        return back()->with('success', 'Leave rejected.');
    }

    private function authorizeApproval(LeaveApplication $leaveApplication): void
    {
        $user = auth()->user();
        $leaveApplication->loadMissing('employee');

        // No one can approve/reject their own leave
        abort_if(
            $user->employee && $leaveApplication->employee_id === $user->employee->id,
            403,
            'You cannot approve or reject your own leave application.'
        );

        if ($user->hasAnyRole(['super_admin', 'hr_admin'])) {
            return;
        }

        if ($user->hasRole('manager')) {
            abort_unless(
                $user->employee?->department_id &&
                $user->employee->department_id === $leaveApplication->employee?->department_id,
                403
            );

            return;
        }

        abort(403);
    }

    private function missingEmployeeProfileResponse()
    {
        return redirect()
            ->route('dashboard')
            ->with('error', 'Your employee profile is not created yet. Please contact HR or Super Admin to complete your profile setup.');
    }
}
