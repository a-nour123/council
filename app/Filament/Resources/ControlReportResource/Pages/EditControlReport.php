<?php

namespace App\Filament\Resources\ControlReportResource\Pages;

use App\Filament\Resources\ControlReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditControlReport extends EditRecord
{
    protected static string $resource = ControlReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
