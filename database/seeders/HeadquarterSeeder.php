<?php

namespace Database\Seeders;

use App\Models\Headquarter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeadquarterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $headquarters = [
            [
                'id' => 6,
                'code' => 'hq-bry',
                'name' => 'مقر بريدة',
                'address' => 'القصيم - بريدة',
            ],
            [
                'id' => 7,
                'code' => 'hq-az',
                'name' => 'مقر عنيزة',
                'address' => 'القصيم - عنيزة',
            ],
        ];

        foreach ($headquarters as $headquarter) {
            Headquarter::create([
                'id' => $headquarter['id'],
                'code' => $headquarter['code'],
                'name' => $headquarter['name'],
                'address' => $headquarter['address'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
