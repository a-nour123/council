<?php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Resources\TopicResource;
use App\Models\Axis;
use App\Models\ClassificationDecision;
use App\Models\ControlReport;
use App\Models\ControlReportFaculty;
use App\Models\Topic;
use App\Models\TopicAxis;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTopic extends EditRecord
{
    protected static string $resource = TopicResource::class;
    protected static string $view = 'filament.resources.topics.pages.updateaxiestopics';


    public $topicTitle;
    public $topicOrder;
    public $topicId;
    public $maintopics;
    public $axisData = [];
    public $allAxis = [];
    public $topicAxis;
    public $axisDatacount;
    public $topic_id_main;
    public $existingReportData;
    public $existingContent;
    public $existingFacultyReportContent;
    public $classificationReference;
    public $EscalationAuthority;
    public $decisions;
    public $allDecisions;
    public $existingDepartmentTopicContent, $existingFacultyTopicContent;

    public function mount($record): void
    {
        parent::mount($record);

        $topic = Topic::find($record);
        // get the title
        $this->topicTitle = $topic->title;
        // get the classification
        $this->classificationReference = $topic->classification_reference;
        $this->EscalationAuthority = $topic->escalation_authority;
        $this->decisions = explode(',', $topic->decisions); // Assuming 'decisions' are stored as a comma-separated string
        $this->allDecisions = ClassificationDecision::all(); // Fetch all available decisions
        $this->topicOrder = $topic->order;

        // get the id of topic
        $this->topicId = $topic->id;
        // get the id of maintopicid column
        $this->topic_id_main = $topic->main_topic_id;
        // get the id of subtopic
        $this->maintopics = Topic::where('id', '!=', $record)->get();
        $this->axisData = TopicAxis::with('axis')
            ->where('topic_id', $this->topicId)
            ->get()
            ->map(function ($topicAxis) {
                return [
                    'axisTitle' => $topicAxis->axis->title,
                    'axisid' => $topicAxis->axis->id,
                    'topicId' => $topicAxis->topic->id,
                    'fieldData' => $topicAxis->field_data,
                ];
            });

        // Fetch all axes titles
        $this->allAxis = Axis::all();
        $this->axisDatacount = 4;
        // dd($this->axisData);
        $existingReportData = ControlReport::where('topic_id', $record)->first();
        $this->existingContent = $existingReportData ? $existingReportData->content : '';
        $existingReportFacultyData = ControlReportFaculty::where('topic_id', $record)->first();
        $this->existingFacultyReportContent = $existingReportFacultyData ? $existingReportFacultyData->content : '';

        $this->existingDepartmentTopicContent = $existingReportData ? $existingReportData->topic_formate : '';
        $this->existingFacultyTopicContent = $existingReportFacultyData ? $existingReportFacultyData->topic_formate : '';
    }
}
