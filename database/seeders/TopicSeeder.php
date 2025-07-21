<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topics = [
            [
                'id' => 22,
                'code' => 'tpc_1',
                'order' => 1,
                'title' => 'شؤون أعضاء هيئة التدريس-التعيين/ الاستقطاب',
                'main_topic_id' => null,
            ],
            [
                'id' => 23,
                'code' => 'tpc_2',
                'order' => 2,
                'title' => 'نظر في المذكرة المرفوعة من رئيس قسم القانون الدولي الخاص بشأن عدم الموافقة على تعيين المواطن عبدالحميد السيد على وظيفة معيد في قسم القانون الدولي الخاص',
                'main_topic_id' => 22,
            ],
            [
                'id' => 24,
                'code' => 'tpc_3',
                'order' => 3,
                'title' => 'شؤون الابتعاث والتدريب - ابتعاث',
                'main_topic_id' => null,
            ],
            [
                'id' => 25,
                'code' => 'tpc_4',
                'order' => 4,
                'title' => 'النظر في المذكرة المرفوعة من رئيس قسم القانون الدولي الخاص بشأن الموافقة على ابتعاث المعيد محمد السيد احمد لدراسة الماجستير لمدة 30',
                'main_topic_id' => 24,
            ],
            [
                'id' => 26,
                'code' => 'tpc_5',
                'order' => 5,
                'title' => 'شؤون أمناء المجلس - التعيين/ الاستقطاب',
                'main_topic_id' => null,
            ],
            [
                'id' => 27,
                'code' => 'tpc_6',
                'order' => 6,
                'title' => 'النظر في المذكرة المرفوعة من رئيس قسم القانون الدولي الخاص بشأن عدم الموافقة على تعيين المواطن عبدالحميد السيد على وظيفة معيد في قسم القانون الدولي الخاص',
                'main_topic_id' => 26,
            ],
            [
                'id' => 28,
                'code' => 'tpc_7',
                'order' => 7,
                'title' => 'شئون المنح للخارج',
                'main_topic_id' => null,
            ],
            [
                'id' => 29,
                'code' => 'tpc_8',
                'order' => 8,
                'title' => 'النظر في المذكرة المرفوعة من رئيس قسم القانون الدولي الخاص بشأن عدم الموافقة على سفر المواطن عبدالحميد السيد على للسفر للخارج لاداء المنحة',
                'main_topic_id' => 28,
            ],
            [
                'id' => 30,
                'code' => 'tpc_9',
                'order' => 9,
                'title' => 'ترقيات- منح',
                'main_topic_id' => 28,
            ],
        ];

        foreach ($topics as $topic) {
            Topic::create([
                'id' => $topic['id'],
                'code' => $topic['code'],
                'order' => $topic['order'],
                'title' => $topic['title'],
                'main_topic_id' => $topic['main_topic_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
