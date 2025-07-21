<?php

namespace App\Filament\Resources\SubmitTopicResource\Pages;

use App\Filament\Resources\SubmitTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSubmitTopic extends CreateRecord
{
    protected static string $resource = SubmitTopicResource::class;
     protected static string $view = 'filament.resources.Agandes.createaganda';

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     dd($data);
    // }

}
