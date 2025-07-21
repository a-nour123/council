<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use App\Models\Department;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // // Get the latest code from the database
        // $latestCode = Department::latest('id')->first()->code  ?? 'dept_0';
        // // Extract the number part from the latest code
        // $latestNumber = intval(preg_replace('/[^0-9]+/', '', $latestCode));

        // // Increment the number
        // $newNumber = $latestNumber + 1;

        // // Generate the new code
        // $newCode = 'dept_' . $newNumber;
        // $data['code'] = $newCode;

        if(auth()->user()->hasRole('Faculty Admin'))
        {
            $data['faculty_id'] = auth()->user()->faculty_id;
        }

        return $data;
    }
}
