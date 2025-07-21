<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassificationDecisionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('classification_decisions')->insert([
            ['name' => 'التوصية بالموافقة'],
            ['name' => 'التوصية بعدم الموافقة'],
            ['name' => 'الموافقة'],
            ['name' => 'عدم الموافقة'],
        ]);
    }
}
