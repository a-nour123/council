<?php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Resources\TopicResource;
use App\Models\Topic;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTopic extends CreateRecord
{
    protected static string $resource = TopicResource::class;
    protected static string $view = 'filament.resources.topics.pages.createaxiestopics';

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // Get the latest code from the database
    //     $latestRecord = Topic::latest('id')->first();
    //     $latestCode = $latestRecord->code ?? 'tpc_0';

    //     // Extract the number part from the latest code
    //     $latestNumber = intval(preg_replace('/[^0-9]+/', '', $latestCode));

    //     // Increment the number
    //     $newNumber = $latestNumber + 1;

    //     // Generate the new code
    //     $newCode = 'tpc_' . $newNumber;
    //     $data['code'] = $newCode;


    //     $latestOrder = intval($latestRecord->order ?? '0');
    //     $data['order'] = $latestOrder + 1;
        
    //     return $data;
    // }
}
