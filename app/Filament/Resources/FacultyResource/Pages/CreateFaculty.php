<?php

namespace App\Filament\Resources\FacultyResource\Pages;

use App\Filament\Resources\FacultyResource;
use App\Models\Faculty;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFaculty extends CreateRecord
{
    protected static string $resource = FacultyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // dd($data);
        // Get the latest code from the database
        // $latestCode = Faculty::latest('id')->first()->code  ?? 'fa_0';

        // // Extract the number part from the latest code
        // $latestNumber = intval(preg_replace('/[^0-9]+/', '', $latestCode));

        // // Increment the number
        // $newNumber = $latestNumber + 1;

        // // Generate the new code
        // $newCode = 'fa_' . $newNumber;
        // $data['code'] = $newCode;
        return $data;
    }

    protected function afterCreate(): void
    {
        // dd($this->data['headquarters']);
        $faculty = $this->record;

        // Attach headquarters
        if (!empty($this->data['headquarters'])) {
            $faculty->headquarters()->attach($this->data['headquarters']);
        }
    }
}
