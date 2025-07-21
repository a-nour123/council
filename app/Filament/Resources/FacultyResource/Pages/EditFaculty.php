<?php

namespace App\Filament\Resources\FacultyResource\Pages;

use App\Filament\Resources\FacultyResource;
use Filament\Actions;
use App\Models\User;
use App\Models\Faculty;
use App\Models\FacultyCouncil;
use App\Models\FacultyHeadquarter;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;

class EditFaculty extends EditRecord
{
    protected static string $resource = FacultyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
            //putting condition to stop delete record if found related data
            Actions\DeleteAction::make()::make()
                ->before(function (Actions\DeleteAction $action, Faculty $record) {
                    if ($record->departments()->exists()) {
                        Notification::make()
                            ->danger()
                            ->color('danger')
                            ->title(__('Failed to delete'))
                            ->body(__('Faculty contains on departments related'))
                            ->seconds(10)
                            ->send();

                            // This will halt and cancel the delete action modal.
                            $action->cancel();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $facultyId = $data['id'];

        $headquarters = FacultyHeadquarter::where('faculty_id', $facultyId)->pluck('headquarter_id');
        $data['headquarters'] = $headquarters;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // dd($data);
        $faculty = $this->record;

        // $currentHeadquarterId = Faculty::where('id',$facultyId)->value('headquarter_id');
        // $newHeadquarterId = $data['headquarter_id'];

        // if($newHeadquarterId != $currentHeadquarterId)
        // {
        //     $facultyCouncilRecords = FacultyCouncil::where('faculty_id',$facultyId)->get();

        //     foreach($facultyCouncilRecords as $record)
        //     {
        //         $record->delete();
        //     }
        // }

        if (!empty($data['headquarters'])) {
            $faculty->headquarters()->sync($data['headquarters']);
        }

        return $data;
    }
}
