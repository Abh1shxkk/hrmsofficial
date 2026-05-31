<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\SalaryStructure;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Departments ──
        $engineering = Department::firstOrCreate(['name' => 'Engineering'], ['description' => 'Software Development Team']);
        $hr = Department::firstOrCreate(['name' => 'Human Resources'], ['description' => 'HR & People Operations']);
        $marketing = Department::firstOrCreate(['name' => 'Marketing'], ['description' => 'Marketing & Growth']);

        // ── HR Admin Employee Profile ──
        $hrUser = User::where('email', 'hr@hrms.com')->first();
        if ($hrUser && !$hrUser->employee) {
            Employee::create([
                'user_id' => $hrUser->id,
                'department_id' => $hr->id,
                'employee_code' => 'EMP001',
                'designation' => 'HR Manager',
                'date_of_birth' => '1988-05-15',
                'date_of_joining' => '2022-01-10',
                'phone' => '9876543210',
                'address' => '123 HR Street, Mumbai',
                'employment_type' => 'full_time',
            ]);
        }

        // ── Manager ──
        $managerUser = User::firstOrCreate(
            ['email' => 'manager@hrms.com'],
            ['name' => 'Rahul Sharma', 'password' => bcrypt('password'), 'role' => 'manager', 'is_active' => true]
        );
        $managerUser->assignRole('manager');
        $managerUser->update(['is_active' => true]);

        $manager = Employee::firstOrCreate(
            ['user_id' => $managerUser->id],
            [
                'department_id' => $engineering->id,
                'employee_code' => 'EMP002',
                'designation' => 'Engineering Manager',
                'date_of_birth' => '1990-03-20',
                'date_of_joining' => '2023-04-01',
                'phone' => '9876543211',
                'address' => '456 Tech Park, Bangalore',
                'employment_type' => 'full_time',
            ]
        );

        // Set manager as department head
        $engineering->update(['manager_id' => $manager->id]);

        // ── Employee 1 ──
        $emp1User = User::firstOrCreate(
            ['email' => 'priya@hrms.com'],
            ['name' => 'Priya Patel', 'password' => bcrypt('password'), 'role' => 'employee', 'is_active' => true]
        );
        $emp1User->assignRole('employee');
        $emp1User->update(['is_active' => true]);

        $emp1 = Employee::firstOrCreate(
            ['user_id' => $emp1User->id],
            [
                'department_id' => $engineering->id,
                'employee_code' => 'EMP003',
                'designation' => 'Software Developer',
                'date_of_birth' => '1995-08-12',
                'date_of_joining' => '2024-01-15',
                'phone' => '9876543212',
                'address' => '789 Dev Lane, Pune',
                'employment_type' => 'full_time',
            ]
        );

        // ── Employee 2 ──
        $emp2User = User::firstOrCreate(
            ['email' => 'amit@hrms.com'],
            ['name' => 'Amit Kumar', 'password' => bcrypt('password'), 'role' => 'employee', 'is_active' => true]
        );
        $emp2User->assignRole('employee');
        $emp2User->update(['is_active' => true]);

        $emp2 = Employee::firstOrCreate(
            ['user_id' => $emp2User->id],
            [
                'department_id' => $engineering->id,
                'employee_code' => 'EMP004',
                'designation' => 'Junior Developer',
                'date_of_birth' => '1998-11-25',
                'date_of_joining' => '2025-02-01',
                'phone' => '9876543213',
                'address' => '321 Code Street, Hyderabad',
                'employment_type' => 'full_time',
            ]
        );

        // ── Employee 3 (Marketing) ──
        $emp3User = User::firstOrCreate(
            ['email' => 'neha@hrms.com'],
            ['name' => 'Neha Gupta', 'password' => bcrypt('password'), 'role' => 'employee', 'is_active' => true]
        );
        $emp3User->assignRole('employee');
        $emp3User->update(['is_active' => true]);

        $emp3 = Employee::firstOrCreate(
            ['user_id' => $emp3User->id],
            [
                'department_id' => $marketing->id,
                'employee_code' => 'EMP005',
                'designation' => 'Marketing Executive',
                'date_of_birth' => '1996-06-30',
                'date_of_joining' => '2024-06-01',
                'phone' => '9876543214',
                'address' => '555 Ad Road, Delhi',
                'employment_type' => 'full_time',
            ]
        );

        // ── Salary Structures ──
        $salaries = [
            // [employee, basic, hra, transport, other]
            [$manager, 50000, 20000, 3000, 2000],
            [$emp1, 35000, 14000, 2000, 1000],
            [$emp2, 18000, 7200, 1500, 800],   // gross = 27500, under 21k basic nahi but gross > 21k, no ESI
            [$emp3, 30000, 12000, 2000, 1000],
        ];

        foreach ($salaries as [$emp, $basic, $hra, $transport, $other]) {
            SalaryStructure::firstOrCreate(
                ['employee_id' => $emp->id, 'is_active' => true],
                [
                    'basic' => $basic,
                    'hra' => $hra,
                    'transport_allowance' => $transport,
                    'other_allowances' => $other,
                    'effective_from' => '2025-04-01',
                ]
            );
        }

        // Also for HR user
        if ($hrUser && $hrUser->employee) {
            SalaryStructure::firstOrCreate(
                ['employee_id' => $hrUser->employee->id, 'is_active' => true],
                [
                    'basic' => 45000,
                    'hra' => 18000,
                    'transport_allowance' => 3000,
                    'other_allowances' => 2000,
                    'effective_from' => '2025-04-01',
                ]
            );
        }

        // ── Leave Balances (current year) ──
        $allEmployees = Employee::all();
        $leaveTypes = LeaveType::all();
        $year = now()->year;

        foreach ($allEmployees as $emp) {
            foreach ($leaveTypes as $type) {
                LeaveBalance::firstOrCreate(
                    ['employee_id' => $emp->id, 'leave_type_id' => $type->id, 'year' => $year],
                    ['allocated' => $type->max_days_per_year, 'used' => 0, 'pending' => 0]
                );
            }
        }

        // ── Demo Attendance (last 10 working days for all employees) ──
        $today = now();
        $date = $today->copy()->subDays(14);
        while ($date->lte($today)) {
            if (!$date->isWeekend()) {
                foreach ($allEmployees as $emp) {
                    $statuses = ['present', 'present', 'present', 'present', 'wfh', 'present', 'half_day'];
                    Attendance::firstOrCreate(
                        ['employee_id' => $emp->id, 'date' => $date->format('Y-m-d')],
                        [
                            'status' => $statuses[array_rand($statuses)],
                            'check_in' => '09:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT),
                            'check_out' => '18:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT),
                            'marked_by' => $hrUser?->id ?? 1,
                        ]
                    );
                }
            }
            $date->addDay();
        }

        // ── Demo Tasks ──
        $tasks = [
            ['title' => 'Setup CI/CD Pipeline', 'assigned_to' => $emp1->id, 'assigned_by' => $managerUser->id, 'priority' => 'high', 'due_date' => now()->addDays(7), 'status' => 'in_progress'],
            ['title' => 'Write Unit Tests for API', 'assigned_to' => $emp2->id, 'assigned_by' => $managerUser->id, 'priority' => 'medium', 'due_date' => now()->addDays(14), 'status' => 'todo'],
            ['title' => 'Code Review - Auth Module', 'assigned_to' => $emp1->id, 'assigned_by' => $managerUser->id, 'priority' => 'low', 'due_date' => now()->addDays(3), 'status' => 'completed'],
            ['title' => 'Prepare Social Media Plan', 'assigned_to' => $emp3->id, 'assigned_by' => $hrUser?->id ?? 1, 'priority' => 'high', 'due_date' => now()->addDays(5), 'status' => 'in_progress'],
        ];

        foreach ($tasks as $task) {
            Task::firstOrCreate(['title' => $task['title']], $task);
        }
    }
}
