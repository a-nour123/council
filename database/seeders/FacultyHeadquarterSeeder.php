<?php

namespace Database\Seeders;

use App\Models\FacultyHeadquarter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultyHeadquarterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facultyHeadquarter = [
            [
                // 'id' => 1,
                'faculty_id' => 10,
                'headquarter_id' => 6,
            ],
            [
                // 'id' => 2,
                'faculty_id' => 10,
                'headquarter_id' => 7,
            ],
            [
                // 'id' => 3,
                'faculty_id' => 11,
                'headquarter_id' => 6,
            ],
            [
                // 'id' => 4,
                'faculty_id' => 11,
                'headquarter_id' => 7,
            ],
            [
                // 'id' => 5,
                'faculty_id' => 12,
                'headquarter_id' => 6,
            ],
            [
                // 'id' => 6,
                'faculty_id' => 12,
                'headquarter_id' => 7,
            ],
            [
                // 'id' => 7,
                'faculty_id' => 13,
                'headquarter_id' => 6,
            ],
            [
                // 'id' => 8,
                'faculty_id' => 13,
                'headquarter_id' => 7,
            ],

        ];

        // DB::table('faculty_headquarter')->insert($facultyHeadquarter);
        FacultyHeadquarter::insert($facultyHeadquarter);
    }
}
