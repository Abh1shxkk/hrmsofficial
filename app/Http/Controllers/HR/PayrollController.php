<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayrollRequest;
use App\Models\Employee;
use App\Models\SalarySlip;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(protected PayrollService $payrollService) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('employee')) {
            if (! $user->employee) {
                return $this->missingEmployeeProfileResponse();
            }

            $query = SalarySlip::with(['employee.user', 'employee.department'])
                ->where('employee_id', $user->employee->id);
        } else {
            $query = SalarySlip::with(['employee.user', 'employee.department']);
        }

        if ($month = $request->get('month')) {
            $query->where('month', $month);
        }
        if ($year = $request->get('year')) {
            $query->where('year', $year);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee.user', fn($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('employee', fn($employeeQuery) => $employeeQuery->where('employee_code', 'like', "%{$search}%"));
            });
        }

        $slips = $query->latest()->paginate(15)->withQueryString();

        return view('payroll.index', compact('slips'));
    }

    public function process()
    {
        $employees = Employee::active()
            ->with(['user', 'salaryStructures'])
            ->orderBy('employee_code')
            ->get();

        $existingSlips = SalarySlip::select('employee_id', 'month', 'year')
            ->get()
            ->groupBy(fn($s) => $s->employee_id . '-' . $s->month . '-' . $s->year)
            ->keys()
            ->toArray();

        return view('payroll.process', compact('employees', 'existingSlips'));
    }

    public function generate(StorePayrollRequest $request)
    {

        $employee = Employee::findOrFail($request->employee_id);

        if ($employee->status !== 'active') {
            return back()->with('error', 'Cannot generate payroll for an inactive or terminated employee.')->withInput();
        }

        $existing = SalarySlip::where('employee_id', $employee->id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($existing) {
            return back()
                ->with('error', 'Slip already generated for ' . $employee->user->name . ' for this month/year.')
                ->withInput();
        }

        try {
            $slip = $this->payrollService->generate($employee, (int) $request->month, (int) $request->year);
            return redirect()->route('salary-slip.show', $slip)
                ->with('success', 'Salary slip generated successfully for ' . $employee->user->name . '.');
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return back()->with('error', 'Salary slip already exists for this employee and period.')->withInput();
            }
            return back()->with('error', 'Database error while generating salary slip.')->withInput();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(SalarySlip $salarySlip)
    {
        if (auth()->user()->hasRole('employee') && ! auth()->user()->employee) {
            return $this->missingEmployeeProfileResponse();
        }

        $this->authorizeSalarySlipAccess($salarySlip);

        $salarySlip->load(['employee.user', 'employee.department']);
        return view('payroll.slip', compact('salarySlip'));
    }

    public function markAsPaid(SalarySlip $salarySlip)
    {
        if ($salarySlip->status === 'paid') {
            return back()->with('error', 'This slip is already marked as paid.');
        }

        $salarySlip->update(['status' => 'paid']);

        return back()->with('success', 'Salary slip marked as paid.');
    }

    public function destroy(SalarySlip $salarySlip)
    {
        if ($salarySlip->status === 'paid') {
            return back()->with('error', 'Cannot delete a paid salary slip.');
        }

        $salarySlip->delete();
        return redirect()->route('payroll.index')->with('success', 'Salary slip deleted.');
    }

    public function download(SalarySlip $salarySlip)
    {
        if (auth()->user()->hasRole('employee') && ! auth()->user()->employee) {
            return $this->missingEmployeeProfileResponse();
        }

        $this->authorizeSalarySlipAccess($salarySlip);

        $salarySlip->load(['employee.user', 'employee.department']);
        $pdf = Pdf::loadView('payroll.slip-pdf', ['slip' => $salarySlip]);
        $filename = 'salary-slip-' . $salarySlip->employee->employee_code
            . '-' . str_pad($salarySlip->month, 2, '0', STR_PAD_LEFT)
            . '-' . $salarySlip->year . '.pdf';

        return $pdf->download($filename);
    }

    private function authorizeSalarySlipAccess(SalarySlip $salarySlip): void
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'hr_admin'])) {
            return;
        }

        abort_unless($user->hasRole('employee') && $salarySlip->employee_id === $user->employee->id, 403);
    }

    private function missingEmployeeProfileResponse()
    {
        return redirect()
            ->route('dashboard')
            ->with('error', 'Your employee profile is not created yet. Please contact HR or Super Admin to complete your profile setup.');
    }
}
