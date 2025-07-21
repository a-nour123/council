<?php

namespace App\Filament\Resources\SubmitTopicResource\Pages;

use App\Filament\Resources\SubmitTopicResource;
use App\Models\AgandesTopicForm;
use App\Models\AgendaImage;
use App\Models\Topic;
use App\Models\TopicAgenda;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditSubmitTopic extends EditRecord
{
    // protected static string $resource = SubmitTopicResource::class;
    protected static string $resource = SubmitTopicResource::class;
     protected static string $view = 'filament.resources.Agandes.editaganda';
    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    public $faculity;
    public $TopicId;
    public $departmentId;
    public $status;
    public $AgendaTopicFormbuilder ;
    public $subTopic ;
    public $mainTopic ;
    public $agendaId ;
    public $notes ;
    public $created_by;
    public $agenda;
    public $photos;
    
    public function mount($record): void
    {

        $agenda = TopicAgenda::find($record);
        $faculity = $agenda->faculty_id;
        $agendaId=$agenda->id;
        $subTopic = $agenda->topic_id;
        $mainTopic=Topic::where('id',$agenda->topic_id)->value('main_topic_id');
        $departmentId= $agenda->department_id;
        $status= $agenda->status;
        $notes= $agenda->note;

        if (!$agenda) {
            abort(404);
        }
        if ( (int) $agenda->status != 0 || auth()->user()->id != $agenda->created_by) {
            abort(403, 'You do not have access to this page.');
        }
        // dd($agenda->departement->faculty->ar_name);
        // get the title
        $this->agenda = $agenda;
        $this->faculity = $faculity;
        $this->TopicId = $subTopic;
        $this->departmentId = $departmentId;
        $this->status = $status;
        $this->notes = $notes;
         $this->mainTopic = $mainTopic;
        $this->subTopic = $subTopic;
        $this->agendaId = $agendaId;
        $this->created_by = $agenda->created_by;
        // get the id of subtopic
        $this->AgendaTopicFormbuilder = AgandesTopicForm::where('agenda_id', $record)->get();
        $this->photos = AgendaImage::where('agenda_id', $agenda->id)->get();
        // dd($this->AgendaTopicFormbuilder);
        parent::mount($record);


    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Agenda Updated successfully';
    }
}
