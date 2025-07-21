<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectObjectivesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projectOjectives = [
            ['name' => 'ضمان ادارة الكليات بواسطة مجالسها وفق اللوائح والأنظمة.'],
            ['name' => 'ضبط وتوحيد طريقة اعداد محاضر المجالس على مستوى الجامعة.'],
            ['name' => 'توحيد صياغة المحاضر بما يتفق مع اللوائح والأنظمة.'],
            ['name' => 'ربط الصلاحيات حسب الجهة بناء على الأنظمة واللوائح فيما يخص شؤون أعضاء هيئة التدريس أو الابتعاث والتدريب أو شؤون الدراسات العليا أو شؤون الطلاب فى مرحلة البكالريوس. '],
            ['name' => 'تسهيل تحديد جهات الرفع أو الاحالة للقرارات والتوصيات فى المجالس.'],
            ['name' => 'ضمان رفع جميع المحاضر إلى الجهة الأعلى واتعمادها.'],
            ['name' => 'تطوير نظام محاضر المجالس بما يحقق الهدف منها.'],
        ];
        DB::table('project_objectives')->insert($projectOjectives);
    }
}
