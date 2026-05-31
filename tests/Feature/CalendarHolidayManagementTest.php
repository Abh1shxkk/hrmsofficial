<?php

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\SalarySlip;
use App\Models\SalaryStructure;
use App\Models\User;
use App\Services\LeaveService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createCalendarEmployee(string $role, Department $department, string $email): Employee
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
        'date_of_birth' => now()->subYears(25)->toDateString(),
        'date_of_joining' => now()->subYear()->toDateString(),
        'phone' => '9999999999',
        'address' => 'Calendar Test Address',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);
}

test('hr can manage national regional and company holidays', function () {
    $department = Department::create(['name' => 'Calendar HR']);
    $hr = createCalendarEmployee('hr_admin', $department, 'calendar.hr@test.local');

    $this->actingAs($hr->user)
        ->post(route('holidays.store'), [
            'name' => 'Foundation Day',
            'date' => '2026-06-03',
            'type' => 'company',
        ])
        ->assertRedirect();

    $holiday = Holiday::first();

    expect($holiday->name)->toBe('Foundation Day')
        ->and($holiday->type)->toBe('company');

    $this->actingAs($hr->user)
        ->delete(route('holidays.destroy', $holiday))
        ->assertRedirect();

    expect(Holiday::count())->toBe(0);
});

test('employee can view calendar but cannot create holidays', function () {
    $department = Department::create(['name' => 'Calendar Employee']);
    $employee = createCalendarEmployee('employee', $department, 'calendar.employee@test.local');
    Holiday::create(['name' => 'Regional Fair', 'date' => '2026-06-10', 'type' => 'regional']);

    $this->actingAs($employee->user)
        ->get(route('holidays.index', ['month' => 6, 'year' => 2026]))
        ->assertOk()
        ->assertSee('Calendar & Holidays')
        ->assertSee('Regional Fair');

    $this->actingAs($employee->user)
        ->post(route('holidays.store'), [
            'name' => 'Unauthorized',
            'date' => '2026-06-11',
            'type' => 'company',
        ])
        ->assertForbidden();
});

test('duplicate holiday date is rejected', function () {
    $department = Department::create(['name' => 'Calendar Duplicate']);
    $hr = createCalendarEmployee('hr_admin', $department, 'calendar.duplicate.hr@test.local');
    Holiday::create(['name' => 'Existing Holiday', 'date' => '2026-06-12', 'type' => 'national']);

    $this->actingAs($hr->user)
        ->post(route('holidays.store'), [
            'name' => 'Duplicate Holiday',
            'date' => '2026-06-12',
            'type' => 'company',
        ])
        ->assertSessionHasErrors('date');

    expect(Holiday::count())->toBe(1);
});

test('attendance cannot be marked on configured holiday', function () {
    $department = Department::create(['name' => 'Attendance Holiday']);
    $hr = createCalendarEmployee('hr_admin', $department, 'attendance.holiday.hr@test.local');
    $employee = createCalendarEmployee('employee', $department, 'attendance.holiday.employee@test.local');
    Holiday::create(['name' => 'Company Offsite', 'date' => '2026-06-15', 'type' => 'company']);

    $this->actingAs($hr->user)
        ->from(route('attendance.index'))
        ->post(route('attendance.mark'), [
            'employee_id' => $employee->id,
            'date' => '2026-06-15',
            'status' => 'present',
        ])
        ->assertRedirect(route('attendance.index'))
        ->assertSessionHas('error');

    expect(Attendance::count())->toBe(0);
});

test('holidays are excluded from leave working day calculation', function () {
    Holiday::create(['name' => 'Regional Holiday', 'date' => '2026-06-03', 'type' => 'regional']);

    $days = app(LeaveService::class)->calculateWorkingDays('2026-06-01', '2026-06-03');

    expect($days)->toEqual(2.0);
});

test('holidays reduce payroll working days for salary slip generation', function () {
    $department = Department::create(['name' => 'Payroll Holiday']);
    $hr = createCalendarEmployee('hr_admin', $department, 'payroll.holiday.hr@test.local');
    $employee = createCalendarEmployee('employee', $department, 'payroll.holiday.employee@test.local');
    Holiday::create(['name' => 'May Company Holiday', 'date' => '2026-05-07', 'type' => 'company']);

    SalaryStructure::create([
        'employee_id' => $employee->id,
        'basic' => 10000,
        'hra' => 5000,
        'transport_allowance' => 1000,
        'other_allowances' => 0,
        'effective_from' => '2026-01-01',
        'is_active' => true,
    ]);

    $this->actingAs($hr->user)->post(route('payroll.generate'), [
        'employee_id' => $employee->id,
        'month' => 5,
        'year' => 2026,
    ]);

    expect(SalarySlip::first()->working_days)->toBe(20);
});
