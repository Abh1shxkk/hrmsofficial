<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            ['name' => 'Republic Day', 'date' => '2026-01-26', 'type' => 'national'],
            ['name' => 'Holi', 'date' => '2026-03-10', 'type' => 'national'],
            ['name' => 'Good Friday', 'date' => '2026-04-03', 'type' => 'national'],
            ['name' => 'Independence Day', 'date' => '2026-08-15', 'type' => 'national'],
            ['name' => 'Gandhi Jayanti', 'date' => '2026-10-02', 'type' => 'national'],
            ['name' => 'Dussehra', 'date' => '2026-10-20', 'type' => 'national'],
            ['name' => 'Diwali', 'date' => '2026-11-08', 'type' => 'national'],
            ['name' => 'Christmas', 'date' => '2026-12-25', 'type' => 'national'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(['date' => $holiday['date']], $holiday);
        }
    }
}
