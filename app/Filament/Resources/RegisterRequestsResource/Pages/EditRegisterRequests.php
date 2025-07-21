<?php

namespace App\Filament\Resources\RegisterRequestsResource\Pages;

use App\Filament\Resources\RegisterRequestsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegisterRequests extends EditRecord
{
    protected static string $resource = RegisterRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
