<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::with('manager.user')->withCount([
            'employees',
            'employees as active_employees_count' => fn ($q) => $q->where('status', 'active'),
        ]);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $departments = $query->orderBy('name')->paginate(15)->withQueryString();
        $summary = [
            'total' => Department::count(),
            'employees' => Employee::count(),
            'active' => Employee::active()->count(),
        ];

        return view('departments.index', compact('departments', 'summary'));
    }

    public function create()
    {
        $managers = $this->managerCandidates();
        return view('departments.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:departments,name',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
        ]);

        Department::create($request->only('name', 'description', 'manager_id'));

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $managers = $this->managerCandidates();
        return view('departments.edit', compact('department', 'managers'));
    }

    public function show(Request $request, Department $department)
    {
        $department->load('manager.user');

        $query = $department->employees()->with('user');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('employment_type')) {
            $query->where('employment_type', $type);
        }

        $employees = $query->orderBy('employee_code')->paginate(15)->withQueryString();
        $report = [
            'total' => $department->employees()->count(),
            'active' => $department->employees()->where('status', 'active')->count(),
            'inactive' => $department->employees()->where('status', 'inactive')->count(),
            'terminated' => $department->employees()->where('status', 'terminated')->count(),
            'full_time' => $department->employees()->where('employment_type', 'full_time')->count(),
            'part_time' => $department->employees()->where('employment_type', 'part_time')->count(),
            'contract' => $department->employees()->where('employment_type', 'contract')->count(),
        ];

        return view('departments.show', compact('department', 'employees', 'report'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
        ]);

        $department->update($request->only('name', 'description', 'manager_id'));

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->count()) {
            return back()->with('error', 'Cannot delete department with employees.');
        }

        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }

    private function managerCandidates()
    {
        return Employee::active()
            ->with('user')
            ->whereHas('user', fn ($q) => $q->whereIn('role', ['manager', 'hr_admin', 'super_admin']))
            ->get();
    }
}
