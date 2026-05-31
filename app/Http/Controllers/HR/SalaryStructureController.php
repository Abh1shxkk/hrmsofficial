<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaryStructureRequest;
use App\Models\Employee;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryStructureController extends Controller
{
    public function index(Request $request)
    {
        $query = SalaryStructure::with(['employee.user', 'employee.department'])
            ->where('is_active', true);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee.user', fn($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('employee', fn($employeeQuery) => $employeeQuery->where('employee_code', 'like', "%{$search}%"));
            });
        }

        $structures = $query->latest('effective_from')->paginate(15)->withQueryString();

        return view('payroll.salary-structure.index', compact('structures'));
    }

    public function create()
    {
        $employees = Employee::active()->with('user')
            ->orderBy('employee_code')
            ->get();

        return view('payroll.salary-structure.create', compact('employees'));
    }

    public function store(StoreSalaryStructureRequest $request)
    {
        DB::transaction(function () use ($request) {
            // Deactivate all previous structures for this employee
            SalaryStructure::where('employee_id', $request->employee_id)
                ->update(['is_active' => false]);

            SalaryStructure::create([
                'employee_id' => $request->employee_id,
                'basic' => $request->basic,
                'hra' => $request->hra,
                'transport_allowance' => $request->transport_allowance ?? 0,
                'other_allowances' => $request->other_allowances ?? 0,
                'effective_from' => $request->effective_from,
                'is_active' => true,
            ]);
        });

        return redirect()->route('salary-structures.index')
            ->with('success', 'Salary structure assigned successfully.');
    }

    public function edit(SalaryStructure $salaryStructure)
    {
        $salaryStructure->load('employee.user');

        $employees = Employee::active()->with('user')
            ->orderBy('employee_code')
            ->get();

        return view('payroll.salary-structure.edit', compact('salaryStructure', 'employees'));
    }

    public function destroy(SalaryStructure $salaryStructure)
    {
        // Don't delete if it's the only structure for this employee
        $otherCount = SalaryStructure::where('employee_id', $salaryStructure->employee_id)
            ->where('id', '!=', $salaryStructure->id)
            ->count();

        if ($otherCount === 0) {
            return back()->with('error', 'Cannot delete the only salary structure for this employee. Update it instead.');
        }

        // If deleting the active structure, activate the most recent remaining one
        if ($salaryStructure->is_active) {
            SalaryStructure::where('employee_id', $salaryStructure->employee_id)
                ->where('id', '!=', $salaryStructure->id)
                ->latest('effective_from')
                ->first()
                ?->update(['is_active' => true]);
        }

        $salaryStructure->delete();
        return redirect()->route('salary-structures.index')->with('success', 'Salary structure deleted.');
    }

    public function update(StoreSalaryStructureRequest $request, SalaryStructure $salaryStructure)
    {
        DB::transaction(function () use ($request, $salaryStructure) {
            // Deactivate all structures for this employee
            SalaryStructure::where('employee_id', $request->employee_id)
                ->update(['is_active' => false]);

            // Update current and mark active
            $salaryStructure->update([
                'employee_id' => $request->employee_id,
                'basic' => $request->basic,
                'hra' => $request->hra,
                'transport_allowance' => $request->transport_allowance ?? 0,
                'other_allowances' => $request->other_allowances ?? 0,
                'effective_from' => $request->effective_from,
                'is_active' => true,
            ]);
        });

        return redirect()->route('salary-structures.index')
            ->with('success', 'Salary structure updated successfully.');
    }
}
