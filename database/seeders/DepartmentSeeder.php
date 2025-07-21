<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'id' => 10,
                'code' => 'dep-ge',
                'ar_name' => 'قسم الجغرافيا',
                'en_name' => 'Department of Geography',
                'faculty_id' => 11,
            ],
            [
                'id' => 13,
                'code' => 'dep-su',
                'ar_name' => 'قسم السنة و علومها',
                'en_name' => 'Department of Sunnah and its Sciences',
                'faculty_id' => 10,
            ],
            [
                'id' => 14,
                'code' => 'dep-mcph',
                'ar_name' => 'قسم الكيمياء الطبية والعقاقير',
                'en_name' => 'Department of Medicinal Chemistry and Pharmacognosy',
                'faculty_id' => 12,
            ],
            [
                'id' => 16,
                'code' => 'dep-psy',
                'ar_name' => 'قسم علم النفس',
                'en_name' => 'Department of Psychology',
                'faculty_id' => 13,
            ],
        ];

        foreach ($departments as $department) {
            Department::create([
                'id' => $department['id'],
                'code' => $department['code'],
                'ar_name' => $department['ar_name'],
                'en_name' => $department['en_name'],
                'faculty_id' => $department['faculty_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    }
}
