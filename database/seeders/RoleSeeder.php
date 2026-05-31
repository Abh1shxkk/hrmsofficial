<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@hrms.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
        $admin->assignRole('super_admin');
        $admin->update([
            'name' => 'Super Admin',
            'is_active' => true,
        ]);

        $hr = User::firstOrCreate(
            ['email' => 'hr@hrms.com'],
            [
                'name' => 'HR Admin',
                'password' => bcrypt('password'),
                'role' => 'hr_admin',
                'is_active' => true,
            ]
        );
        $hr->assignRole('hr_admin');
        $hr->update([
            'name' => 'HR Admin',
            'is_active' => true,
        ]);
    }
}
