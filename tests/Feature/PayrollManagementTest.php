<?php

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\SalarySlip;
use App\Models\SalaryStructure;
use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPayrollEmployee(string $role, Department $department, string $email): Employee
{
    $user = User::factory()->create([
        'name' => ucfirst(str_replace('_', ' ', $role)),
        'email' => $email,
        'role' => $role,
        'is_active' => true,
    ]);

    return Employee::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'employee_code' => strtoupper(substr($role, 0, 3)) . random_int(1000, 9999),
        'designation' => ucfirst($role),
        'date_of_birth' => now()->subYears(28)->toDateString(),
        'date_of_joining' => now()->subYear()->toDateString(),
        'phone' => '9999999999',
        'address' => 'Payroll Test Address',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);
}

function assignPayrollStructure(Employee $employee, array $overrides = []): SalaryStructure
{
    return SalaryStructure::create(array_merge([
        'employee_id' => $employee->id,
        'basic' => 20000,
        'hra' => 8000,
        'transport_allowance' => 2000,
        'other_allowances' => 1000,
        'effective_from' => '2026-01-01',
        'is_active' => true,
    ], $overrides));
}

function createPayrollSlip(Employee $employee, array $overrides = []): SalarySlip
{
    $admin = User::factory()->create(['role' => 'hr_admin', 'is_active' => true]);

    return SalarySlip::create(array_merge([
        'employee_id' => $employee->id,
        'month' => 5,
        'year' => 2026,
        'basic' => 20000,
        'hra' => 8000,
        'transport_allowance' => 2000,
        'other_allowances' => 1000,
        'gross_salary' => 31000,
        'pf_employee' => 2400,
        'pf_employer' => 2400,
        'esi_employee' => 0,
        'esi_employer' => 0,
        'tds' => 300,
        'total_deductions' => 2700,
        'net_salary' => 28300,
        'working_days' => 21,
        'present_days' => 2,
        'status' => 'processed',
        'generated_by' => $admin->id,
    ], $overrides));
}

test('payroll calculation includes basic hra allowances pf esi tds and net pay', function () {
    $calculation = app(PayrollService::class)->calculate(10000, 5000, 1000, 1000);

    expect($calculation['gross_salary'])->toBe(17000.0)
        ->and($calculation['pf_employee'])->toBe(1200.0)
        ->and($calculation['pf_employer'])->toBe(1200.0)
        ->and($calculation['esi_employee'])->toBe(127.5)
        ->and($calculation['esi_employer'])->toBe(552.5)
        ->and($calculation['tds'])->toBe(0.0)
        ->and($calculation['total_deductions'])->toBe(1327.5)
        ->and($calculation['net_salary'])->toBe(15672.5);
});

test('payroll calculation applies tds slab and skips esi above gross threshold', function () {
    $calculation = app(PayrollService::class)->calculate(80000, 40000, 5000, 5000);

    expect($calculation['gross_salary'])->toBe(130000.0)
        ->and($calculation['pf_employee'])->toBe(9600.0)
        ->and($calculation['esi_employee'])->toBe(0.0)
        ->and($calculation['esi_employer'])->toBe(0.0)
        ->and($calculation['tds'])->toBe(13166.67)
        ->and($calculation['total_deductions'])->toBe(22766.67)
        ->and($calculation['net_salary'])->toBe(107233.33);
});

test('hr can generate monthly salary slip with allowance snapshot and attendance counts', function () {
    $department = Department::create(['name' => 'Payroll']);
    $hr = createPayrollEmployee('hr_admin', $department, 'payroll.hr@test.local');
    $employee = createPayrollEmployee('employee', $department, 'payroll.employee@test.local');
    assignPayrollStructure($employee, [
        'basic' => 10000,
        'hra' => 5000,
        'transport_allowance' => 1000,
        'other_allowances' => 1000,
    ]);

    Attendance::create(['employee_id' => $employee->id, 'date' => '2026-05-04', 'status' => 'present', 'marked_by' => $hr->user_id]);
    Attendance::create(['employee_id' => $employee->id, 'date' => '2026-05-05', 'status' => 'wfh', 'marked_by' => $hr->user_id]);
    Attendance::create(['employee_id' => $employee->id, 'date' => '2026-05-06', 'status' => 'absent', 'marked_by' => $hr->user_id]);
    Holiday::create(['name' => 'May Holiday', 'date' => '2026-05-07', 'type' => 'company']);

    $this->actingAs($hr->user)
        ->post(route('payroll.generate'), [
            'employee_id' => $employee->id,
            'month' => 5,
            'year' => 2026,
        ])
        ->assertRedirect();

    $slip = SalarySlip::where('employee_id', $employee->id)->first();

    expect($slip)->not->toBeNull()
        ->and($slip->transport_allowance)->toEqual('1000.00')
        ->and($slip->other_allowances)->toEqual('1000.00')
        ->and($slip->gross_salary)->toEqual('17000.00')
        ->and($slip->pf_employee)->toEqual('1200.00')
        ->and($slip->esi_employee)->toEqual('127.50')
        ->and($slip->total_deductions)->toEqual('1327.50')
        ->and($slip->net_salary)->toEqual('15672.50')
        ->and($slip->present_days)->toBe(2)
        ->and($slip->generated_by)->toBe($hr->user_id);
});

test('duplicate payroll generation for same employee month and year is blocked', function () {
    $department = Department::create(['name' => 'Finance']);
    $hr = createPayrollEmployee('hr_admin', $department, 'duplicate.hr@test.local');
    $employee = createPayrollEmployee('employee', $department, 'duplicate.employee@test.local');
    assignPayrollStructure($employee);

    $this->actingAs($hr->user)->post(route('payroll.generate'), [
        'employee_id' => $employee->id,
        'month' => 5,
        'year' => 2026,
    ]);

    $this->actingAs($hr->user)
        ->from(route('payroll.process'))
        ->post(route('payroll.generate'), [
            'employee_id' => $employee->id,
            'month' => 5,
            'year' => 2026,
        ])
        ->assertRedirect(route('payroll.process'));

    expect(SalarySlip::where('employee_id', $employee->id)->count())->toBe(1);
});

test('employee can only view own salary slip and manager cannot access salary slip by url', function () {
    $department = Department::create(['name' => 'Security']);
    $employee = createPayrollEmployee('employee', $department, 'own.slip@test.local');
    $otherEmployee = createPayrollEmployee('employee', $department, 'other.slip@test.local');
    $manager = createPayrollEmployee('manager', $department, 'manager.slip@test.local');
    $ownSlip = createPayrollSlip($employee);
    $otherSlip = createPayrollSlip($otherEmployee, ['month' => 6]);

    $this->actingAs($employee->user)->get(route('salary-slip.show', $ownSlip))->assertOk();
    $this->actingAs($employee->user)->get(route('salary-slip.show', $otherSlip))->assertForbidden();
    $this->actingAs($manager->user)->get(route('salary-slip.show', $ownSlip))->assertForbidden();
});

test('employee can access own payroll history only', function () {
    $department = Department::create(['name' => 'Employee Payroll History']);
    $employee = createPayrollEmployee('employee', $department, 'history.own.employee@test.local');
    $otherEmployee = createPayrollEmployee('employee', $department, 'history.other.employee@test.local');
    createPayrollSlip($employee, ['month' => 4]);
    createPayrollSlip($otherEmployee, ['month' => 6]);

    $this->actingAs($employee->user)
        ->get(route('payroll.index'))
        ->assertOk()
        ->assertSee($employee->employee_code)
        ->assertDontSee($otherEmployee->employee_code);
});

test('hr can view payroll history and download salary slip pdf', function () {
    $department = Department::create(['name' => 'History']);
    $hr = createPayrollEmployee('hr_admin', $department, 'history.hr@test.local');
    $employee = createPayrollEmployee('employee', $department, 'history.employee@test.local');
    $slip = createPayrollSlip($employee);

    $this->actingAs($hr->user)
        ->get(route('payroll.index', ['search' => $employee->employee_code]))
        ->assertOk()
        ->assertSee($employee->employee_code);

    $this->actingAs($hr->user)
        ->get(route('salary-slip.download', $slip))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('salary structure validation rejects negative salary components', function () {
    $department = Department::create(['name' => 'Validation']);
    $hr = createPayrollEmployee('hr_admin', $department, 'validation.hr@test.local');
    $employee = createPayrollEmployee('employee', $department, 'validation.employee@test.local');

    $this->actingAs($hr->user)
        ->post(route('salary-structures.store'), [
            'employee_id' => $employee->id,
            'basic' => -1,
            'hra' => -1,
            'transport_allowance' => -1,
            'other_allowances' => -1,
            'effective_from' => '2026-01-01',
        ])
        ->assertSessionHasErrors(['basic', 'hra', 'transport_allowance', 'other_allowances']);
});

test('payroll generation uses salary structure effective for selected month', function () {
    $department = Department::create(['name' => 'Revision']);
    $hr = createPayrollEmployee('hr_admin', $department, 'revision.hr@test.local');
    $employee = createPayrollEmployee('employee', $department, 'revision.employee@test.local');

    assignPayrollStructure($employee, [
        'basic' => 10000,
        'hra' => 5000,
        'transport_allowance' => 1000,
        'other_allowances' => 0,
        'effective_from' => '2026-01-01',
        'is_active' => false,
    ]);
    assignPayrollStructure($employee, [
        'basic' => 30000,
        'hra' => 12000,
        'transport_allowance' => 2000,
        'other_allowances' => 1000,
        'effective_from' => '2026-06-01',
        'is_active' => true,
    ]);

    $this->actingAs($hr->user)->post(route('payroll.generate'), [
        'employee_id' => $employee->id,
        'month' => 5,
        'year' => 2026,
    ]);

    $slip = SalarySlip::where('employee_id', $employee->id)->first();

    expect($slip->basic)->toEqual('10000.00')
        ->and($slip->hra)->toEqual('5000.00')
        ->and($slip->transport_allowance)->toEqual('1000.00')
        ->and($slip->gross_salary)->toEqual('16000.00');
});

test('employee cannot process payroll or generate salary slips', function () {
    $department = Department::create(['name' => 'Access']);
    $employee = createPayrollEmployee('employee', $department, 'access.employee@test.local');

    $this->actingAs($employee->user)->get(route('payroll.process'))->assertForbidden();
    $this->actingAs($employee->user)->post(route('payroll.generate'), [
        'employee_id' => $employee->id,
        'month' => 5,
        'year' => 2026,
    ])->assertForbidden();
});

test('super admin can view any employee salary slip', function () {
    $department = Department::create(['name' => 'Super Payroll']);
    $superAdmin = createPayrollEmployee('super_admin', $department, 'payroll.super@test.local');
    $employee = createPayrollEmployee('employee', $department, 'payroll.super.employee@test.local');
    $slip = createPayrollSlip($employee);

    $this->actingAs($superAdmin->user)->get(route('salary-slip.show', $slip))->assertOk();
});
