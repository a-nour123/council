<?php

namespace Database\Seeders;

use App\Models\FacultyCouncil;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacultyCouncilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facultyCouncils = [
            [
                'id' => 10,
                'user_id' => 35,
                'faculty_id' => 12,
                'position_id' => 5,
            ],
            [
                'id' => 11,
                'user_id' => 36,
                'faculty_id' => 12,
                'position_id' => 4,
            ],
            [
                'id' => 12,
                'user_id' => 37,
                'faculty_id' => 12,
                'position_id' => 1,
            ],
            [
                'id' => 13,
                'user_id' => 29,
                'faculty_id' => 13,
                'position_id' => 5,
            ],
            [
                'id' => 14,
                'user_id' => 30,
                'faculty_id' => 13,
                'position_id' => 4,
            ],
            [
                'id' => 15,
                'user_id' => 31,
                'faculty_id' => 13,
                'position_id' => 1,
            ],
            [
                'id' => 16,
                'user_id' => 32,
                'faculty_id' => 10,
                'position_id' => 5,
            ],
            [
                'id' => 17,
                'user_id' => 33,
                'faculty_id' => 10,
                'position_id' => 4,

            ],
            [
                'id' => 18,
                'user_id' => 34,
                'faculty_id' => 10,
                'position_id' => 1,
            ],
            [
                'id' => 19,
                'user_id' => 38,
                'faculty_id' => 11,
                'position_id' => 5,
            ],
            [
                'id' => 20,
                'user_id' => 39,
                'faculty_id' => 11,
                'position_id' => 4,
            ],
            [
                'id' => 21,
                'user_id' => 40,
                'faculty_id' => 11,
                'position_id' => 1,
            ],
        ];

        foreach ($facultyCouncils as $facultyCouncil) {
            // FacultyCouncil::create($facultyCouncil);
            FacultyCouncil::create([
                'id' => $facultyCouncil['id'],
                'user_id' => $facultyCouncil['user_id'],
                'faculty_id' => $facultyCouncil['faculty_id'],
                'position_id' => $facultyCouncil['position_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
