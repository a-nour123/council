<?php

namespace Database\Seeders;

use App\Models\Axis;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AxisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $axes = [
            [
                'id' => 5,
                'title' => 'بيانات الموضوع',
                'content' => '[{"type":"text","required":"false","label":"الدرجة&nbsp;","className":"form-control","name":"text-1719443048913-0","access":"false","subtype":"text"},{"type":"text","required":"false","label":"الكلية","className":"form-control","name":"text-1719443569850-0","access":"false","subtype":"text"},{"type":"textarea","required":"false","label":"نبذة عن مشروع تخرجك","className":"form-control","name":"textarea-1719443579780-0","access":"false","subtype":"textarea"}]',
            ],
            [
                'id' => 6,
                'title' => 'بيانات صاحب العلاقة',
                'content' => '[{"type":"textarea","required":false,"label":"الاسم","className":"form-control","name":"textarea-1719443249833-0","access":false,"subtype":"textarea"},{"type":"textarea","required":false,"label":"البكالوريوس","className":"form-control","name":"textarea-1719443251513-0","access":false,"subtype":"textarea"},{"type":"date","required":false,"label":"تاريخ التخرج","className":"form-control","name":"date-1719443254240-0","access":false,"subtype":"date"},{"type":"textarea","required":false,"label":"التقدير","className":"form-control","name":"textarea-1719443256666-0","access":false,"subtype":"textarea"}]',
            ],
            [
                'id' => 7,
                'title' => 'بيانات توصية مجلس القسم المختص',
                'content' => '[{"type":"checkbox-group","required":false,"label":"Checkbox Group","toggle":false,"inline":false,"name":"checkbox-group-1719443334992-0","access":false,"other":false,"values":[{"label":"رئيس","value":"رئيس","selected":true},{"label":"رئيسة","value":"رئيسة","selected":false},{"label":"مشرف","value":"مشرف","selected":false},{"label":"مشرفة","value":"مشرفة","selected":false}]},{"type":"select","required":false,"label":"Select","className":"form-control","name":"select-1719443399344-0","access":false,"multiple":false,"values":[{"label":"قسم القانون الدولي الخاص","value":"option-1","selected":true},{"label":"قسم القانون الدولي العام","value":"option-2","selected":false},{"label":"قسم القضاء العام","value":"option-3","selected":false}]},{"type":"textarea","required":false,"label":"التوصية","className":"form-control","name":"textarea-1719443456275-0","access":false,"subtype":"textarea"}]',
            ],
            [
                'id' => 8,
                'title' => 'اللوائح المنظمة',
                'content' => '[{"type":"text","required":false,"label":"<span id=\"ContentPlaceHolder1_dvAddTopicContentScholarshipEdit_lblRegulationsScholarEdit\" style=\"color: rgb(0, 0, 0); font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; text-align: -webkit-right;\">إيحاء الأبتعاث والتدريب للمنسبين الجامعيين</span><span style=\"color: rgb(0, 0, 0); font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; text-align: -webkit-right;\"><br style=\"color: rgb(0, 0, 0); font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; text-align: -webkit-right;\"></span><span id=\"ContentPlaceHolder1_dvAddTopicContentScholarshipEdit_Label48\" style=\"color: rgb(0, 0, 0); font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; text-align: -webkit-right;\">المواد</span>","className":"form-control","name":"text-1719443737246-0","access":false,"subtype":"text"},{"type":"text","required":false,"label":"<span style=\"color: rgb(0, 0, 0); font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; text-align: -webkit-right;\">أستنادات أخري أن وجدت</span>","className":"form-control","name":"text-1719443763174-0","access":false,"subtype":"text"}]',
            ],
        ];

        foreach ($axes as $axie) {
            Axis::create([
                'id' => $axie['id'],
                'title' => $axie['title'],
                'content' => $axie['content'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
