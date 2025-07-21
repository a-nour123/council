<?php

namespace Database\Seeders;

use App\Models\YearlyCalendar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class YearlyCalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $yearlyCalendars = [
            [
                'id' => 1,
                'code' => 'yq-m-1',
                'name' => 'العام الاساسي',
                'start_date' => '2024-06-13',
                'end_date' => '2024-07-05',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'code' => 'yq-s-1',
                'name' => 'العام الصيفي',
                'start_date' => '2024-08-22',
                'end_date' => '2024-09-20',
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($yearlyCalendars as $yearlyCalendar) {
            YearlyCalendar::create($yearlyCalendar);
        }
    }
}
