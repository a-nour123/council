<?php

namespace App\Filament\Resources\SubmitTopicResource\Pages;

use App\Filament\Resources\SubmitTopicResource;
use App\Models\AgandesTopicForm;
use App\Models\AgendaImage;
use App\Models\Topic;
use App\Models\TopicAgenda;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class RequestDetails extends Page
{
    protected static string $resource = SubmitTopicResource::class;
    protected static string $view = 'filament.resources.Agandes.viewAganda';
    public $faculty;
    public $department;
    public $TopicId;
    public $departmentId;
    public $status;
    public $updates;
    public $rejectReason;
    public $AgendaTopicFormbuilder;
    public $subTopic;
    public $mainTopic;
    public $agendaId;
    public $notes;
    public $created_by;
    public $acadimic_year;
    public $mainTopicTitle;
    public $subTopicTitle;
    public $recordId;
    public $photos;
    public $agenda;
     public $facultyId;
    public function mount($recordId): void
    {
        // Find the agenda record
        $agenda = TopicAgenda::find($recordId);

        // Retrieve and assign data to class properties
        $this->faculty = $agenda->faculty->ar_name;
        $this->agendaId = $agenda->id;
        $this->departmentId = $agenda->department_id;
        $this->facultyId = $agenda->faculty_id;
        $this->subTopic = $agenda->topic_id;
        $this->subTopicTitle = Topic::where('id', $this->subTopic)->value('title');

        $this->mainTopic = Topic::where('id', $this->subTopic)->value('main_topic_id');
        $this->mainTopicTitle = Topic::where('id', $this->mainTopic)->value('title');

        $this->department = $agenda->departement->ar_name;

        // Status assignment
        $this->status = match ($agenda->status) {
            0 => 'قيد الانتظار',
            1 => 'مقبول',
            2 => 'مرفوض',
        };

        $this->updates = match ($agenda->updates) {
            0 => 'قيد الانتظار',
            1 => 'تم الموافقة علي الطلب',
            2 => 'تم رفض الطلب',
            3 => 'موافقة مجلس القسم',
            5 => 'رفض مجلس القسم',
            4 => 'موافقة مجلس الكلية',
            6 => 'رفض مجلس الكلية',
        };

        // Other properties
        $this->rejectReason = $agenda->note;
        $this->notes = $agenda->note;
        $this->acadimic_year = $agenda->academic_year->name ?? 'غير معروف';
        $this->created_by = $agenda->created_by;

        // Form builder data
        $this->AgendaTopicFormbuilder = AgandesTopicForm::where('agenda_id', $recordId)->get();
        $this->photos = AgendaImage::where('agenda_id', $recordId)->get();
        $this->agenda = $agenda;

    }

    public function getTitle(): string|Htmlable
    {
        return __('View Agenda Details');
    }
}
