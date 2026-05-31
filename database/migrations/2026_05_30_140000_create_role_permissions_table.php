<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('module');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_manage')->default(false);
            $table->timestamps();

            $table->unique(['role', 'module']);
        });

        $now = now();
        $rows = [];
        foreach ($this->defaults() as $role => $modules) {
            foreach ($modules as $module => $permissions) {
                $rows[] = [
                    'role' => $role,
                    'module' => $module,
                    'can_view' => $permissions['view'],
                    'can_edit' => $permissions['edit'],
                    'can_delete' => $permissions['delete'],
                    'can_manage' => $permissions['manage'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('role_permissions')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }

    private function defaults(): array
    {
        $modules = ['User Management', 'Employees', 'Departments', 'Attendance', 'Leaves', 'Payroll', 'Tasks', 'Holidays'];

        $defaults = [];
        foreach (['super_admin', 'hr_admin', 'manager', 'employee'] as $role) {
            foreach ($modules as $module) {
                $defaults[$role][$module] = ['view' => false, 'edit' => false, 'delete' => false, 'manage' => false];
            }
        }

        foreach ($modules as $module) {
            $defaults['super_admin'][$module] = ['view' => true, 'edit' => true, 'delete' => true, 'manage' => true];
        }

        foreach (['Employees', 'Departments', 'Attendance', 'Leaves', 'Payroll', 'Tasks', 'Holidays'] as $module) {
            $defaults['hr_admin'][$module] = ['view' => true, 'edit' => true, 'delete' => true, 'manage' => true];
        }

        foreach (['Attendance', 'Leaves', 'Tasks', 'Holidays'] as $module) {
            $defaults['manager'][$module] = ['view' => true, 'edit' => $module === 'Tasks', 'delete' => $module === 'Tasks', 'manage' => $module === 'Tasks'];
        }
        $defaults['manager']['Employees'] = ['view' => true, 'edit' => false, 'delete' => false, 'manage' => false];

        foreach (['Attendance', 'Leaves', 'Tasks', 'Holidays', 'Payroll'] as $module) {
            $defaults['employee'][$module] = ['view' => true, 'edit' => in_array($module, ['Leaves', 'Tasks'], true), 'delete' => false, 'manage' => false];
        }

        return $defaults;
    }
};
