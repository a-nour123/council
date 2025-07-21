<?php

namespace App\Filament\Resources\YearlyCalendarResource\Pages;

use App\Filament\Resources\YearlyCalendarResource;
use App\Models\YearlyCalendar;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateYearlyCalendar extends CreateRecord
{
    protected static string $resource = YearlyCalendarResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // // Get the latest code from the database
        // $latestCode = YearlyCalendar::latest('id')->first()->code ?? 'yq_0';


        // // Extract the number part from the latest code
        // $latestNumber = intval(preg_replace('/[^0-9]+/', '', $latestCode));

        // // Increment the number
        // $newNumber = $latestNumber + 1;

        // // Generate the new code
        // $newCode = 'yq_' . $newNumber;
        // $data['code'] = $newCode;
        return $data;
    }
}
