<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TopicAxis;

class TopicAxisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topic_axes = [
            [
                'id' => 8,
                'topic_id' => 23,
                'axis_id' => 5,
                'field_data' => '[{"type":"text","required":true,"label":"الدرجة&nbsp;","className":"form-control","name":"text-1719443048913-0","access":true,"subtype":"text"},{"type":"text","required":true,"label":"الكلية","className":"form-control","name":"text-1719443569850-0","access":true,"subtype":"text"},{"type":"textarea","required":true,"label":"نبذة عن مشروع تخرجك","className":"form-control","name":"textarea-1719443579780-0","access":true,"subtype":"textarea"}]',
            ],
            [
                'id' => 9,
                'topic_id' => 23,
                'axis_id' => 6,
                'field_data' => '[{"type":"textarea","required":false,"label":"الاسم","className":"form-control","name":"textarea-1719443249833-0","access":false,"subtype":"textarea"},{"type":"textarea","required":false,"label":"البكالوريوس","className":"form-control","name":"textarea-1719443251513-0","access":false,"subtype":"textarea"},{"type":"date","required":false,"label":"تاريخ التخرج","className":"form-control","name":"date-1719443254240-0","access":false,"subtype":"date"},{"type":"textarea","required":false,"label":"التقدير","className":"form-control","name":"textarea-1719443256666-0","access":false,"subtype":"textarea"}]',
            ],
            [
                'id' => 10,
                'topic_id' => 23,
                'axis_id' => 7,
                'field_data' => '[{"type":"checkbox-group","required":false,"label":"Checkbox Group","toggle":false,"inline":false,"name":"checkbox-group-1719443334992-0","access":false,"other":false,"values":[{"label":"رئيس","value":"رئيس","selected":true},{"label":"رئيسة","value":"رئيسة","selected":false},{"label":"مشرف","value":"مشرف","selected":false},{"label":"مشرفة","value":"مشرفة","selected":false}]},{"type":"select","required":false,"label":"Select","className":"form-control","name":"select-1719443399344-0","access":false,"multiple":false,"values":[{"label":"قسم القانون الدولي الخاص","value":"option-1","selected":true},{"label":"قسم القانون الدولي العام","value":"option-2","selected":false},{"label":"قسم القضاء العام","value":"option-3","selected":false}]},{"type":"textarea","required":false,"label":"التوصية","className":"form-control","name":"textarea-1719443456275-0","access":false,"subtype":"textarea"}]',
            ],
            [
                'id' => 11,
                'topic_id' => 25,
                'axis_id' => 5,
                'field_data' => '[{"type":"text","required":true,"label":"الدرجة&nbsp;","className":"form-control","name":"text-1719443048913-0","access":true,"subtype":"text"},{"type":"text","required":true,"label":"الكلية","className":"form-control","name":"text-1719443569850-0","access":true,"subtype":"text"},{"type":"textarea","required":true,"label":"نبذة عن مشروع تخرجك","className":"form-control","name":"textarea-1719443579780-0","access":true,"subtype":"textarea"}]',
            ],
            [
                'id' => 12,
                'topic_id' => 25,
                'axis_id' => 8,
                'field_data' => '[{"type":"text","required":false,"label":"<span id=\"ContentPlaceHolder1_dvAddTopicContentScholarshipEdit_lblRegulationsScholarEdit\" style=\"color: rgb(0, 0, 0); font-family: \" helvetica=\"\" neue\",\"=\"\" helvetica,\"=\"\" arial,\"=\"\" sans-serif;\"=\"\" text-align:=\"\" -webkit-right;\"=\"\"><span>إيحاء الأبتعاث والتدريب لمنسوبي الجامعات</span><br style=\"color: rgb(0, 0, 0); font-family: \" helvetica=\"\" neue\",\"=\"\" helvetica,\"=\"\" arial,\"=\"\" sans-serif;\"=\"\" text-align:=\"\" -webkit-right;\"=\"\"><span id=\"ContentPlaceHolder1_dvAddTopicContentScholarshipEdit_Label48\" style=\"color: rgb(0, 0, 0); font-family: \" helvetica=\"\" neue\",\"=\"\" helvetica,\"=\"\" arial,\"=\"\" sans-serif;\"=\"\" text-align:=\"\" -webkit-right;\"=\"\">المواد</span>","className":"form-control","name":"text-1719443737246-0","access":false,"subtype":"text"},{"type":"text","required":false,"label":"<span style=\"color: rgb(0, 0, 0); font-family: \" helvetica=\"\" neue\",\"=\"\" helvetica,\"=\"\" arial,\"=\"\" sans-serif;\"=\"\" text-align:=\"\" -webkit-right;\"=\"\">أستنادات أخرة إن وجدت</span>","className":"form-control","name":"text-1719443763174-0","access":false,"subtype":"text"}]',
            ],
            [
                'id' => 13,
                'topic_id' => 27,
                'axis_id' => 5,
                'field_data' => '[{"type":"text","required":true,"label":"الدرجة&nbsp;","className":"form-control","name":"text-1719443048913-0","access":true,"subtype":"text"},{"type":"text","required":true,"label":"الكلية","className":"form-control","name":"text-1719443569850-0","access":true,"subtype":"text"},{"type":"textarea","required":true,"label":"نبذة عن مشروع تخرجك","className":"form-control","name":"textarea-1719443579780-0","access":true,"subtype":"textarea"}]',
            ],
            [
                'id' => 14,
                'topic_id' => 27,
                'axis_id' => 6,
                'field_data' => '[{"type":"textarea","required":false,"label":"الاسم","className":"form-control","name":"textarea-1719443249833-0","access":false,"subtype":"textarea"},{"type":"textarea","required":false,"label":"البكالوريوس","className":"form-control","name":"textarea-1719443251513-0","access":false,"subtype":"textarea"},{"type":"date","required":false,"label":"تاريخ التخرج","className":"form-control","name":"date-1719443254240-0","access":false,"subtype":"date"},{"type":"textarea","required":false,"label":"التقدير","className":"form-control","name":"textarea-1719443256666-0","access":false,"subtype":"textarea"}]',
            ],
            [
                'id' => 15,
                'topic_id' => 29,
                'axis_id' => 5,
                'field_data' => '[{"type":"text","required":true,"label":"الدرجة&nbsp;","className":"form-control","name":"text-1719443048913-0","access":true,"subtype":"text"},{"type":"text","required":true,"label":"الكلية","className":"form-control","name":"text-1719443569850-0","access":true,"subtype":"text"},{"type":"textarea","required":true,"label":"نبذة عن مشروع تخرجك","className":"form-control","name":"textarea-1719443579780-0","access":true,"subtype":"textarea"}]',
            ],
            [
                'id' => 16,
                'topic_id' => 29,
                'axis_id' => 6,
                'field_data' => '[{"type":"textarea","required":false,"label":"الاسم","className":"form-control","name":"textarea-1719443249833-0","access":false,"subtype":"textarea"},{"type":"textarea","required":false,"label":"البكالوريوس","className":"form-control","name":"textarea-1719443251513-0","access":false,"subtype":"textarea"},{"type":"date","required":false,"label":"تاريخ التخرج","className":"form-control","name":"date-1719443254240-0","access":false,"subtype":"date"},{"type":"textarea","required":false,"label":"التقدير","className":"form-control","name":"textarea-1719443256666-0","access":false,"subtype":"textarea"}]',
            ],
            [
                'id' => 17,
                'topic_id' => 30,
                'axis_id' => 6,
                'field_data' => '[{"type":"textarea","required":false,"label":"الاسم","className":"form-control","name":"textarea-1719443249833-0","access":false,"subtype":"textarea"},{"type":"textarea","required":false,"label":"البكالوريوس","className":"form-control","name":"textarea-1719443251513-0","access":false,"subtype":"textarea"},{"type":"date","required":false,"label":"تاريخ التخرج","className":"form-control","name":"date-1719443254240-0","access":false,"subtype":"date"},{"type":"textarea","required":false,"label":"التقدير","className":"form-control","name":"textarea-1719443256666-0","access":false,"subtype":"textarea"},{"type":"date","required":false,"label":"تاريخ التوظيف<br>","className":"form-control","name":"date-1719477020029-0","access":false,"subtype":"date"}]',
            ],
            [
                'id' => 18,
                'topic_id' => 30,
                'axis_id' => 7,
                'field_data' => '[{"type":"checkbox-group","required":false,"label":"Checkbox Group","toggle":false,"inline":false,"name":"checkbox-group-1719443334992-0","access":false,"other":false,"values":[{"label":"رئيس","value":"رئيس","selected":true},{"label":"رئيسة","value":"رئيسة","selected":false},{"label":"مشرف","value":"مشرف","selected":false},{"label":"مشرفة","value":"مشرفة","selected":false}]},{"type":"select","required":false,"label":"Select","className":"form-control","name":"select-1719443399344-0","access":false,"multiple":false,"values":[{"label":"قسم القانون الدولي الخاص","value":"option-1","selected":false},{"label":"قسم القانون الدولي العام","value":"option-2","selected":false},{"label":"قسم القضاء العام","value":"option-3","selected":false}]},{"type":"textarea","required":false,"label":"التوصية","className":"form-control","name":"textarea-1719443456275-0","access":false,"subtype":"textarea"}]',
            ],
        ];

        foreach ($topic_axes as $topic_axis) {
            TopicAxis::create([
                'id' => $topic_axis['id'],
                'topic_id' => $topic_axis['topic_id'],
                'axis_id' => $topic_axis['axis_id'],
                'field_data' => $topic_axis['field_data'],
                'created_at' => Null,
                'updated_at' => Null,
            ]);
        }
    }
}
