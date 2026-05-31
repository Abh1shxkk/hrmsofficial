<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTaskEmployee(string $role, Department $department, string $email): Employee
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
        'address' => 'Task Test Address',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);
}

function createTaskFor(Employee $employee, User $assignedBy, array $overrides = []): Task
{
    return Task::create(array_merge([
        'title' => 'Prepare task report',
        'description' => 'Prepare weekly status report',
        'assigned_to' => $employee->id,
        'assigned_by' => $assignedBy->id,
        'status' => 'todo',
        'priority' => 'medium',
        'due_date' => now()->addDays(3)->toDateString(),
    ], $overrides));
}

test('manager can assign task only to employee in own department with priority and due date', function () {
    $department = Department::create(['name' => 'Engineering Tasks']);
    $manager = createTaskEmployee('manager', $department, 'task.manager@test.local');
    $employee = createTaskEmployee('employee', $department, 'task.employee@test.local');

    $dueDate = now()->addDays(5)->toDateString();

    $this->actingAs($manager->user)
        ->post(route('tasks.store'), [
            'title' => 'Build payroll report',
            'description' => 'Prepare payroll task report',
            'assigned_to' => $employee->id,
            'priority' => 'high',
            'due_date' => $dueDate,
        ])
        ->assertRedirect(route('tasks.index'));

    $task = Task::first();

    expect($task->title)->toBe('Build payroll report')
        ->and($task->assigned_to)->toBe($employee->id)
        ->and($task->assigned_by)->toBe($manager->user_id)
        ->and($task->priority)->toBe('high')
        ->and($task->due_date->toDateString())->toBe($dueDate)
        ->and($task->status)->toBe('todo');
});

test('manager cannot assign task to another department employee', function () {
    $managerDepartment = Department::create(['name' => 'Task Sales']);
    $otherDepartment = Department::create(['name' => 'Task Support']);
    $manager = createTaskEmployee('manager', $managerDepartment, 'task.scope.manager@test.local');
    $otherEmployee = createTaskEmployee('employee', $otherDepartment, 'task.scope.employee@test.local');

    $this->actingAs($manager->user)
        ->post(route('tasks.store'), [
            'title' => 'Cross department task',
            'assigned_to' => $otherEmployee->id,
            'priority' => 'medium',
            'due_date' => now()->addDays(2)->toDateString(),
        ])
        ->assertForbidden();

    expect(Task::count())->toBe(0);
});

test('hr can assign task to any active employee', function () {
    $hrDepartment = Department::create(['name' => 'Task HR']);
    $employeeDepartment = Department::create(['name' => 'Task Finance']);
    $hr = createTaskEmployee('hr_admin', $hrDepartment, 'task.hr@test.local');
    $employee = createTaskEmployee('employee', $employeeDepartment, 'task.hr.employee@test.local');

    $this->actingAs($hr->user)
        ->post(route('tasks.store'), [
            'title' => 'Submit finance data',
            'assigned_to' => $employee->id,
            'priority' => 'low',
            'due_date' => now()->addDays(4)->toDateString(),
        ])
        ->assertRedirect(route('tasks.index'));

    expect(Task::first()->assigned_to)->toBe($employee->id)
        ->and(Task::first()->assigned_by)->toBe($hr->user_id);
});

test('employee can view and update only assigned task status', function () {
    $department = Department::create(['name' => 'Task Visibility']);
    $manager = createTaskEmployee('manager', $department, 'task.visibility.manager@test.local');
    $employee = createTaskEmployee('employee', $department, 'task.visibility.employee@test.local');
    $otherEmployee = createTaskEmployee('employee', $department, 'task.visibility.other@test.local');
    $ownTask = createTaskFor($employee, $manager->user, ['title' => 'Own visible task']);
    $otherTask = createTaskFor($otherEmployee, $manager->user, ['title' => 'Other hidden task']);

    $this->actingAs($employee->user)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Own visible task')
        ->assertDontSee('Other hidden task');

    $this->actingAs($employee->user)
        ->patch(route('tasks.update-status', $ownTask), ['status' => 'in_progress'])
        ->assertRedirect();

    $this->actingAs($employee->user)
        ->patch(route('tasks.update-status', $otherTask), ['status' => 'completed'])
        ->assertForbidden();

    expect($ownTask->refresh()->status)->toBe('in_progress')
        ->and($otherTask->refresh()->status)->toBe('todo');
});

test('employee cannot access task assignment create or store routes', function () {
    $department = Department::create(['name' => 'Task Employee Access']);
    $employee = createTaskEmployee('employee', $department, 'task.create.employee@test.local');

    $this->actingAs($employee->user)->get(route('tasks.create'))->assertForbidden();
    $this->actingAs($employee->user)
        ->post(route('tasks.store'), [
            'title' => 'Invalid task',
            'assigned_to' => $employee->id,
            'priority' => 'low',
            'due_date' => now()->addDay()->toDateString(),
        ])
        ->assertForbidden();
});

test('manager can update and delete own assigned task but not another managers task', function () {
    $department = Department::create(['name' => 'Task Manager Control']);
    $manager = createTaskEmployee('manager', $department, 'task.control.manager@test.local');
    $otherManager = createTaskEmployee('manager', $department, 'task.control.other.manager@test.local');
    $employee = createTaskEmployee('employee', $department, 'task.control.employee@test.local');
    $ownTask = createTaskFor($employee, $manager->user, ['title' => 'Own managed task']);
    $otherTask = createTaskFor($employee, $otherManager->user, ['title' => 'Other manager task']);

    $this->actingAs($manager->user)
        ->patch(route('tasks.update-status', $ownTask), ['status' => 'completed'])
        ->assertRedirect();

    $this->actingAs($manager->user)
        ->patch(route('tasks.update-status', $otherTask), ['status' => 'completed'])
        ->assertForbidden();

    $this->actingAs($manager->user)
        ->delete(route('tasks.destroy', $otherTask))
        ->assertForbidden();

    $this->actingAs($manager->user)
        ->delete(route('tasks.destroy', $ownTask))
        ->assertRedirect(route('tasks.index'));

    $this->assertDatabaseMissing('tasks', ['id' => $ownTask->id]);
    expect($otherTask->refresh()->status)->toBe('todo');
});

test('task validation enforces valid status priority and non past due date', function () {
    $department = Department::create(['name' => 'Task Validation']);
    $hr = createTaskEmployee('hr_admin', $department, 'task.validation.hr@test.local');
    $employee = createTaskEmployee('employee', $department, 'task.validation.employee@test.local');
    $task = createTaskFor($employee, $hr->user);

    $this->actingAs($hr->user)
        ->post(route('tasks.store'), [
            'title' => '',
            'assigned_to' => $employee->id,
            'priority' => 'urgent',
            'due_date' => now()->subDay()->toDateString(),
        ])
        ->assertSessionHasErrors(['title', 'priority', 'due_date']);

    $this->actingAs($employee->user)
        ->patch(route('tasks.update-status', $task), ['status' => 'blocked'])
        ->assertSessionHasErrors('status');
});
