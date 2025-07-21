<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\Department_Council;
use App\Models\Faculty;
use App\Models\Headquarter;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // this function for customize the default data at edit mode
    public function mutateFormDataBeforeFill(array $data): array
    {
        // calling faculty model
        $faculty = new Faculty;

        // reterive the headquarter_id by using faculty_id
        // $headquarterId = $faculty->getHeadquarterId($data['faculty_id']);

        // // add key named Headquarter in form data containing the headquarter_id
        // $data['Headquarter'] = $headquarterId;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $departmentId = $data['id'];
        $currentFacultyId = Department::where('id', $departmentId)->value('faculty_id');
        $updatedFacultyId = $data['faculty_id'];

        if($currentFacultyId != $updatedFacultyId)
        {
            $departmentCouncilRecords = Department_Council::where('department_id',$departmentId)->get();

            foreach($departmentCouncilRecords as $record)
            {
                $record->delete();
            }
        }

        return $data;
    }
}
