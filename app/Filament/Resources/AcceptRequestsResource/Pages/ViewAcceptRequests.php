<?php

namespace App\Filament\Resources\AcceptRequestsResource\Pages;

use App\Filament\Resources\AcceptRequestsResource;
use App\Models\AgandesTopicForm;
use App\Models\AgendaImage;
use App\Models\Topic;
use App\Models\TopicAgenda;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewAcceptRequests extends ViewRecord
{
    // protected static string $resource = SubmitTopicResource::class;
    protected static string $resource = AcceptRequestsResource::class;
    protected static string $view = 'filament.resources.Agandes.viewAganda';
    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    public $faculty;
    public $department;
    public $TopicId;
    public $departmentId;
    public $status;
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
    public $updates;
    public $photos;
    public $agenda;
     public $facultyId;
    public function mount($record): void
    {
        parent::mount($record);

        $agenda = TopicAgenda::find($record);
        $faculty = $agenda->faculty->ar_name;
        $agendaId = $agenda->id;

        $subTopic = $agenda->topic_id;
        $subTopicTitle = Topic::where('id', $subTopic)->value('title');

        $mainTopicId = Topic::where('id', $agenda->topic_id)->value('main_topic_id');
        $mainTopicTitle = Topic::where('id', $mainTopicId)->value('title');
        $mainTopic = Topic::where('id', $agenda->topic_id)->value('main_topic_id');

        $department = $agenda->departement->ar_name;
        $this->departmentId = $agenda->department_id;
        $this->facultyId = $agenda->faculty_id;
          // if($agenda->status == 0){
        //     $status = 'قيد الانتظار';
        // }elseif($agenda->status == 1){
        //     $status = 'موافقة';
        // }else{
        //     $status = 'مرفوض';
        // }
        // $status = $agenda->status;
        $rejectReason = $agenda->note;
        $notes = $agenda->note;
        $acadimic_year = $agenda->academic_year->name ?? "غير معروف";

        // get the title
        $this->faculty = $faculty;
        $this->TopicId = $subTopic;
        $this->department = $department;

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
        $this->notes = $notes;
        $this->mainTopic = $mainTopic;
        $this->subTopic = $subTopic;
        $this->agendaId = $agendaId;
        $this->created_by = $agenda->created_by;
        $this->acadimic_year = $acadimic_year;
        $this->mainTopicTitle = $mainTopicTitle;
        $this->subTopicTitle = $subTopicTitle;
        $this->rejectReason = $rejectReason;
        // get the id of subtopic
        $this->AgendaTopicFormbuilder = AgandesTopicForm::where('agenda_id', $record)->get();
        // dd($this->AgendaTopicFormbuilder);
        $this->photos = AgendaImage::where('agenda_id', $record)->get();
        $this->agenda = $agenda;

    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Agenda Updated successfully';
    }

    public function getTitle(): string|Htmlable
    {
        return __('View Agenda Details');
    }
}
