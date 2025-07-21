<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            ['name' => 'Academic Staff', 'ar_name' => 'عضو هيئة تدريس'],
            ['name' => 'Secretary of the Department Council', 'ar_name' => 'أمين مجلس قسم'],
            ['name' => 'Head of Department', 'ar_name' => 'رئيس قسم'],
            ['name' => 'Secretary of the College Council', 'ar_name' => 'امين مجلس كلية'],
            ['name' => 'Dean of the College', 'ar_name' => 'عميد كلية'],
            ['name' => 'Vice Rector for Educational Affairs', 'ar_name' => 'نائب رئيس الجامعة للشؤون التعليمية'],
            ['name' => 'Prex', 'ar_name' => 'رئيس الجامعة'],
        ];
        DB::table('positions')->insert($positions);
    }
}
