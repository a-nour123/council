<?php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Resources\TopicResource;
use App\Models\Axis;
use App\Models\ControlReport;
use App\Models\CoverLetter as ModelsCoverLetter;
use App\Models\Topic;
use App\Models\TopicAxis;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use  App\Models\CoverLetterReport;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CoverLetter extends Page
{
    protected static string $resource = TopicResource::class;
    protected static string $view = 'filament.resources.topics.pages.CoverLetteraxiestopics';


    public $topicTitle;
    public $topicId;
    public $maintopics;
    public $axisData = [];
    public $allAxis = [];
    public $topicAxis ;
    public $axisDatacount ;
    public $topic_id_main ;
    public $existingReportData;
    public $existingContent;

    public function mount($record): void
    {

        $topic = Topic::find($record);
        // get the title
        $this->topicTitle = $topic->title;
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
            $this->axisDatacount=4;
            $existingReportData = CoverLetterReport::where('topic_id', $record)->first();
            $this->existingContent = $existingReportData ? $existingReportData->content : '';

        }

        public function getTitle(): string|Htmlable
        {
            return __('CoverLetter');
        }
}
