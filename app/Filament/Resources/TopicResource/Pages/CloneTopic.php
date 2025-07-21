<?php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Resources\TopicResource;
use App\Models\Axis;
use App\Models\ControlReport;
use App\Models\ControlReportFaculty;
use App\Models\Topic;
use App\Models\TopicAxis;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Page;

class CloneTopic extends Page
{

    protected static string $resource = TopicResource::class;
    protected static string $view = 'filament.resources.topics.pages.cloneaxiestopics';


    public $topicTitle;
    public $topicId;
    public $maintopics;
    public $axisData = [];
    public $allAxis = [];
    public $topicAxis;
    public $axisDatacount;
    public $topic_id_main;
    public $existingReportData;
    public $existingContent;
    public $classificationReference;
    public $existingFacultyReportContent;
    
    public function mount($record): void
    {

        $topic = Topic::find($record);
        $existingReportData = ControlReport::where('topic_id', $record)->first();
        $existingReportFacultyData = ControlReportFaculty::where('topic_id', $record)->first();
        
        $titleSuffix = 'مكرر';

        do {
            // Generate a random number
            $randomNumber = rand(1000, 9999); // You can adjust the range as needed

            // Concatenate the title with the suffix and random number
            $title = $topic->title . $titleSuffix . ' ' . $randomNumber;

            // Check if the title already exists in the database
            $titleExists = Topic::where('title', $title)->exists();
        } while ($titleExists);

        $latestRecord = Topic::latest('id')->first();
        $latestCode = $latestRecord->code ?? 'tpc_0';
        $latestNumber = intval(preg_replace('/[^0-9]+/', '', $latestCode));
        $newNumber = $latestNumber + 1;
        $newCode = 'tpc_' . $newNumber;
        $latestOrder = intval($latestRecord->order ?? '0');
        $newOrder = $latestOrder + 1;

        if (is_null($topic->main_topic_id)) {
            $newTopic = new Topic();
            $newTopic->title = $title;
            $newTopic->code = $newCode;
            $newTopic->order = $newOrder;
            $newTopic->classification_reference = $topic->classification_reference;
            $newTopic->save();
        } else {
            $formData = TopicAxis::where('topic_id', $record)->get('field_data'); // JSON string
            $AxisId=TopicAxis::where('topic_id', $record)->first()->axis_id;
            $mainTopicId = $topic->main_topic_id;

            $newTopic = new Topic();
            $newTopic->title = $title;
            $newTopic->code = $newCode;
            $newTopic->order = $newOrder;
            $newTopic->main_topic_id = $mainTopicId;
            $newTopic->classification_reference = $topic->classification_reference;
            $newTopic->save();
            $topicId = $newTopic->id;

            if ($formData) {
                $decodedFormData = json_decode($formData, true);

                if (is_array($decodedFormData) && !empty($decodedFormData)) {

                    foreach ($decodedFormData as $axisId => $content) {

                        if (!empty($content)) {
                            foreach ($content as &$field) {
                                if (isset($field['label'])) {
                                    // Clean up the label
                                    $field['label'] = preg_replace('/&nbsp;|<br\s*\/?>/', '', $field['label']);
                                    $field['label'] = trim($field['label']);
                                }
                            }

                            $axis = Axis::find($AxisId);
                             if ($axis) {
                                $newTopic->axes()->attach($AxisId, ['field_data' => json_encode($content)]);
                            }
                        }
                    }
                }
             }
             if ($existingReportData) {
                ControlReport::create([
                    'topic_id' => $topicId,
                    'content' => $existingReportData->content
                ]);
            }
            if ($existingReportFacultyData) {
                ControlReportFaculty::create([
                    'topic_id' => $topicId,
                    'content' => $existingReportFacultyData->content
                ]);
            }
        }

    }
}
