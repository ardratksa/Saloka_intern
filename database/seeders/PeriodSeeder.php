<?php

namespace Database\Seeders;

use App\Models\Period;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            [
                'name'       => 'Pagi 1',
                'time_start' => '09:00:00',
                'time_end'   => '09:59:00',
            ],
            [
                'name'       => 'Pagi 2',
                'time_start' => '11:00:00',
                'time_end'   => '11:59:00',
            ],
            [
                'name'       => 'Siang',
                'time_start' => '13:00:00',
                'time_end'   => '13:59:00',
            ],
            [
                'name'       => 'Sore',
                'time_start' => '17:00:00',
                'time_end'   => '17:59:00',
            ],
        ];

        foreach ($periods as $period) {
            Period::create([...$period, 'is_active' => true]);
        }
    }
}