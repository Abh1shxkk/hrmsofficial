<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class LeaveApplicationController extends Controller
{
    public function __construct(protected LeaveService $leaveService) {}

    public function index(Request $request)
    {
        if (! auth()->user()->employee) {
            return $this->missingEmployeeProfileResponse();
        }

        $query = LeaveApplication::with('leaveType')
            ->where('employee_id', auth()->user()->employee->id);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->get('leave_type_id')) {
            $query->where('leave_type_id', $type);
        }

        $applications = $query->latest()->paginate(15)->withQueryString();
        $leaveTypes = LeaveType::all();

        return view('leaves.index', compact('applications', 'leaveTypes'));
    }

    public function create()
    {
        if (! auth()->user()->employee) {
            return $this->missingEmployeeProfileResponse();
        }

        if ($employee = auth()->user()->employee) {
            $this->leaveService->ensureBalancesForEmployee($employee, now()->year);
        }

        $leaveTypes = LeaveType::all();
        return view('leaves.apply', compact('leaveTypes'));
    }

    public function store(StoreLeaveRequest $request)
    {

        $employee = auth()->user()->employee;
        if (!$employee) {
            return $this->missingEmployeeProfileResponse();
        }

        try {
            $this->leaveService->apply($employee, $request->only('leave_type_id', 'from_date', 'to_date', 'reason'));
            return redirect()->route('leaves.my')->with('success', 'Leave application submitted.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    private function missingEmployeeProfileResponse()
    {
        return redirect()
            ->route('dashboard')
            ->with('error', 'Your employee profile is not created yet. Please contact HR or Super Admin to complete your profile setup.');
    }
}
