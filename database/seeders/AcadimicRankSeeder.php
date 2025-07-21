<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcadimicRankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranks = [
            ['name' => 'Teaching assistant', 'ar_name' => 'معيد'],
            ['name' => 'Lecturer', 'ar_name' => 'محاضر'],
            ['name' => 'Assistant Professor', 'ar_name' => 'أستاذ مساعد'],
            ['name' => 'Associate Professor', 'ar_name' => 'أستاذ مشارك'],
            ['name' => 'Professor', 'ar_name' => 'أستاذ'],
        ];
        DB::table('acadimic_ranks')->insert($ranks);
    }
}
