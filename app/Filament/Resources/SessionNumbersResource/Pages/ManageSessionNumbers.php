<?php

namespace App\Filament\Resources\SessionNumbersResource\Pages;

use App\Filament\Resources\SessionNumbersResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSessionNumbers extends ManageRecords
{
    protected static string $resource = SessionNumbersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
