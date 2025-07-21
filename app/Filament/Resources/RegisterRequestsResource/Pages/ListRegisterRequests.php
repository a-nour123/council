<?php

namespace App\Filament\Resources\RegisterRequestsResource\Pages;

use App\Filament\Resources\RegisterRequestsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegisterRequests extends ListRecords
{
    protected static string $resource = RegisterRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
