<?php

namespace App\Filament\Resources\HeadquarterResource\Pages;

use App\Filament\Resources\HeadquarterResource;
use App\Models\Headquarter;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHeadquarter extends CreateRecord
{
    protected static string $resource = HeadquarterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // // Get the latest code from the database
        // $latestCode = Headquarter::latest('id')->first()->code ?? 'hq_0';


        // // Extract the number part from the latest code
        // $latestNumber = intval(preg_replace('/[^0-9]+/', '', $latestCode));

        // // Increment the number
        // $newNumber = $latestNumber + 1;

        // // Generate the new code
        // $newCode = 'hq_' . $newNumber;
        // $data['code'] = $newCode;
        return $data;
    }
}
