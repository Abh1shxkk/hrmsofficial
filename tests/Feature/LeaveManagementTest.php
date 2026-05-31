<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createLeaveEmployee(string $role, Department $department, string $email): Employee
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
        'employee_code' => strtoupper(substr($role, 0, 3)) . random_int(100, 999),
        'designation' => ucfirst($role),
        'date_of_birth' => now()->subYears(25)->toDateString(),
        'date_of_joining' => now()->subYear()->toDateString(),
        'phone' => '9999999999',
        'address' => 'Test Address',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);
}

function seedLeaveTypesForTest(): array
{
    return [
        LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'max_days_per_year' => 12, 'is_carry_forward' => false]),
        LeaveType::create(['name' => 'Sick Leave', 'code' => 'SL', 'max_days_per_year' => 12, 'is_carry_forward' => false]),
        LeaveType::create(['name' => 'Earned Leave', 'code' => 'EL', 'max_days_per_year' => 15, 'is_carry_forward' => true]),
    ];
}

test('employee leave application moves days from balance to pending', function () {
    [$casualLeave] = seedLeaveTypesForTest();
    $department = Department::create(['name' => 'Engineering']);
    $employee = createLeaveEmployee('employee', $department, 'employee.leave@test.local');

    $this->actingAs($employee->user)
        ->post(route('leaves.store'), [
            'leave_type_id' => $casualLeave->id,
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-02',
            'reason' => 'Family work',
        ])
        ->assertRedirect(route('leaves.my'));

    $balance = LeaveBalance::where('employee_id', $employee->id)
        ->where('leave_type_id', $casualLeave->id)
        ->where('year', 2026)
        ->first();

    expect($balance->pending)->toEqual('2.00')
        ->and($balance->used)->toEqual('0.00')
        ->and($balance->balance)->toEqual('10.00');
});

test('manager approval moves pending leave to used balance for own department', function () {
    [$casualLeave] = seedLeaveTypesForTest();
    $department = Department::create(['name' => 'Operations']);
    $manager = createLeaveEmployee('manager', $department, 'manager.leave@test.local');
    $employee = createLeaveEmployee('employee', $department, 'team.employee@test.local');

    $this->actingAs($employee->user)->post(route('leaves.store'), [
        'leave_type_id' => $casualLeave->id,
        'from_date' => '2026-06-03',
        'to_date' => '2026-06-04',
        'reason' => 'Medical appointment',
    ]);

    $application = LeaveApplication::first();

    $this->actingAs($manager->user)
        ->post(route('leaves.approve', $application))
        ->assertRedirect();

    $application->refresh();
    $balance = LeaveBalance::where('employee_id', $employee->id)
        ->where('leave_type_id', $casualLeave->id)
        ->where('year', 2026)
        ->first();

    expect($application->status)->toBe('approved')
        ->and($application->approved_by)->toBe($manager->user_id)
        ->and($balance->pending)->toEqual('0.00')
        ->and($balance->used)->toEqual('2.00')
        ->and($balance->balance)->toEqual('10.00');
});

test('hr rejection releases pending leave balance', function () {
    [$casualLeave] = seedLeaveTypesForTest();
    $department = Department::create(['name' => 'People']);
    $hr = createLeaveEmployee('hr_admin', $department, 'hr.leave@test.local');
    $employee = createLeaveEmployee('employee', $department, 'reject.employee@test.local');

    $this->actingAs($employee->user)->post(route('leaves.store'), [
        'leave_type_id' => $casualLeave->id,
        'from_date' => '2026-06-05',
        'to_date' => '2026-06-05',
        'reason' => 'Personal work',
    ]);

    $application = LeaveApplication::first();

    $this->actingAs($hr->user)
        ->post(route('leaves.reject', $application), ['rejection_reason' => 'Coverage required'])
        ->assertRedirect();

    $application->refresh();
    $balance = LeaveBalance::where('employee_id', $employee->id)
        ->where('leave_type_id', $casualLeave->id)
        ->where('year', 2026)
        ->first();

    expect($application->status)->toBe('rejected')
        ->and($application->rejection_reason)->toBe('Coverage required')
        ->and($balance->pending)->toEqual('0.00')
        ->and($balance->used)->toEqual('0.00')
        ->and($balance->balance)->toEqual('12.00');
});

test('manager cannot approve leave outside own department', function () {
    [$casualLeave] = seedLeaveTypesForTest();
    $managerDepartment = Department::create(['name' => 'Sales']);
    $employeeDepartment = Department::create(['name' => 'Support']);
    $manager = createLeaveEmployee('manager', $managerDepartment, 'other.manager@test.local');
    $employee = createLeaveEmployee('employee', $employeeDepartment, 'other.employee@test.local');

    $this->actingAs($employee->user)->post(route('leaves.store'), [
        'leave_type_id' => $casualLeave->id,
        'from_date' => '2026-06-08',
        'to_date' => '2026-06-08',
        'reason' => 'Personal work',
    ]);

    $application = LeaveApplication::first();

    $this->actingAs($manager->user)
        ->post(route('leaves.approve', $application))
        ->assertForbidden();

    expect($application->refresh()->status)->toBe('pending');
});

test('super admin can approve leave from any department', function () {
    [$casualLeave] = seedLeaveTypesForTest();
    $adminDepartment = Department::create(['name' => 'Admin']);
    $employeeDepartment = Department::create(['name' => 'Finance']);
    $superAdmin = createLeaveEmployee('super_admin', $adminDepartment, 'super.leave@test.local');
    $employee = createLeaveEmployee('employee', $employeeDepartment, 'finance.employee@test.local');

    $this->actingAs($employee->user)->post(route('leaves.store'), [
        'leave_type_id' => $casualLeave->id,
        'from_date' => '2026-06-09',
        'to_date' => '2026-06-09',
        'reason' => 'Bank work',
    ]);

    $application = LeaveApplication::first();

    $this->actingAs($superAdmin->user)
        ->post(route('leaves.approve', $application))
        ->assertRedirect();

    $balance = LeaveBalance::where('employee_id', $employee->id)
        ->where('leave_type_id', $casualLeave->id)
        ->where('year', 2026)
        ->first();

    expect($application->refresh()->status)->toBe('approved')
        ->and($application->approved_by)->toBe($superAdmin->user_id)
        ->and($balance->pending)->toEqual('0.00')
        ->and($balance->used)->toEqual('1.00')
        ->and($balance->balance)->toEqual('11.00');
});

test('overlapping pending or approved leave cannot be applied twice', function () {
    [$casualLeave] = seedLeaveTypesForTest();
    $department = Department::create(['name' => 'Product']);
    $employee = createLeaveEmployee('employee', $department, 'overlap.employee@test.local');

    $payload = [
        'leave_type_id' => $casualLeave->id,
        'from_date' => '2026-06-10',
        'to_date' => '2026-06-11',
        'reason' => 'Personal work',
    ];

    $this->actingAs($employee->user)->post(route('leaves.store'), $payload)->assertRedirect(route('leaves.my'));

    $this->actingAs($employee->user)
        ->from(route('leaves.apply'))
        ->post(route('leaves.store'), [
            'leave_type_id' => $casualLeave->id,
            'from_date' => '2026-06-11',
            'to_date' => '2026-06-12',
            'reason' => 'Duplicate dates',
        ])
        ->assertRedirect(route('leaves.apply'));

    expect(LeaveApplication::count())->toBe(1);
});

test('earned leave carries previous year remaining balance into new year', function () {
    [, , $earnedLeave] = seedLeaveTypesForTest();
    $department = Department::create(['name' => 'Research']);
    $employee = createLeaveEmployee('employee', $department, 'carry.employee@test.local');

    LeaveBalance::create([
        'employee_id' => $employee->id,
        'leave_type_id' => $earnedLeave->id,
        'year' => 2025,
        'allocated' => 15,
        'carried_forward' => 0,
        'used' => 5,
        'pending' => 0,
        'balance' => 0,
    ]);

    app(App\Services\LeaveService::class)->ensureBalancesForEmployee($employee, 2026);

    $balance = LeaveBalance::where('employee_id', $employee->id)
        ->where('leave_type_id', $earnedLeave->id)
        ->where('year', 2026)
        ->first();

    expect($balance->allocated)->toEqual('15.00')
        ->and($balance->carried_forward)->toEqual('10.00')
        ->and($balance->balance)->toEqual('25.00');
});
