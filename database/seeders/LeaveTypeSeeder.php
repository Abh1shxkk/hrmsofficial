<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Casual Leave', 'code' => 'CL', 'max_days_per_year' => 12, 'is_carry_forward' => false],
            ['name' => 'Sick Leave', 'code' => 'SL', 'max_days_per_year' => 12, 'is_carry_forward' => false],
            ['name' => 'Earned Leave', 'code' => 'EL', 'max_days_per_year' => 15, 'is_carry_forward' => true],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
