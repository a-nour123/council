<?php

namespace Database\Seeders;

use App\Models\Department_Council;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Department_CouncilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departmentCouncils = [
            [
                'id' => 23,
                'user_id' => 45,
                'department_id' => 10,
                'position_id' => 3,
            ],
            [
                'id' => 24,
                'user_id' => 46,
                'department_id' => 10,
                'position_id' => 2,
            ],
            [
                'id' => 25,
                'user_id' => 47,
                'department_id' => 10,
                'position_id' => 1,
            ],
            [
                'id' => 29,
                'user_id' => 48,
                'department_id' => 14,
                'position_id' => 3,
            ],
            [
                'id' => 30,
                'user_id' => 49,
                'department_id' => 14,
                'position_id' => 2,
            ],
            [
                'id' => 31,
                'user_id' => 50,
                'department_id' => 14,
                'position_id' => 1,
            ],
            [
                'id' => 32,
                'user_id' => 51,
                'department_id' => 16,
                'position_id' => 3,
            ],
            [
                'id' => 33,
                'user_id' => 52,
                'department_id' => 16,
                'position_id' => 2,
            ],
            [
                'id' => 34,
                'user_id' => 53,
                'department_id' => 16,
                'position_id' => 1,
            ],
            [
                'id' => 35,
                'user_id' => 44,
                'department_id' => 13,
                'position_id' => 3,
            ],
            [
                'id' => 36,
                'user_id' => 42,
                'department_id' => 13,
                'position_id' => 2,
            ],
            [
                'id' => 37,
                'user_id' => 43,
                'department_id' => 13,
                'position_id' => 1,
            ],
        ];

        foreach ($departmentCouncils as $departmentCouncil) {
            Department_Council::create([
                'id' => $departmentCouncil['id'],
                'user_id' => $departmentCouncil['user_id'],
                'department_id' => $departmentCouncil['department_id'],
                'position_id' => $departmentCouncil['position_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
