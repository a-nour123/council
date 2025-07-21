<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faculties = [
            [
                'id' => 10,
                'code' => 'fa-sis',
                'ar_name' => 'كلية الشريعه والدراسات الأسلامية ببريدة',
                'en_name' => 'College of Sharia and Islamic Studies in Buraidah',
                // 'headquarter_id' => 6,
            ],
            [
                'id' => 11,
                'code' => 'fa-as',
                'ar_name' => 'كلية الأداب والعلوم ببريدة',
                'en_name' => 'College of Arts and Sciences in Buraidah',
                // 'headquarter_id' => 6,
            ],
            [
                'id' => 12,
                'code' => 'fa-ph',
                'ar_name' => 'كلية الصيدلة بعنيزة ',
                'en_name' => 'college of Pharmacy in Unayzah ',
                // 'headquarter_id' => 7,
            ],
            [
                'id' => 13,
                'code' => 'fa-hu',
                'ar_name' => 'كلية اللغات والعلوم الإنسانية بعنيزة',
                'en_name' => 'college of Humanity in Unazah',
                // 'headquarter_id' => 7,
            ],
        ];

        foreach ($faculties as $faculty) {
            Faculty::create([
                'id' => $faculty['id'],
                'code' => $faculty['code'],
                'ar_name' => $faculty['ar_name'],
                'en_name' => $faculty['en_name'],
                // 'headquarter_id' => $faculty['headquarter_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
