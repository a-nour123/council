<?php

namespace App\Filament\Resources\AcceptRequestsResource\Pages;

use App\Filament\Resources\AcceptRequestsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAcceptRequests extends EditRecord
{
    protected static string $resource = AcceptRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
