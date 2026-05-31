<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rolesByEmail = [
            'admin@hrms.com' => 'super_admin',
            'hr@hrms.com' => 'hr_admin',
            'manager@hrms.com' => 'manager',
            'priya@hrms.com' => 'employee',
            'amit@hrms.com' => 'employee',
            'neha@hrms.com' => 'employee',
        ];

        foreach ($rolesByEmail as $email => $role) {
            DB::table('users')
                ->where('email', $email)
                ->update([
                    'role' => $role,
                    'is_active' => true,
                ]);
        }
    }

    public function down(): void
    {
        //
    }
};
