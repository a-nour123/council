<?php

namespace App\Filament\Resources\YearlyCalendarResource\Pages;

use App\Filament\Resources\YearlyCalendarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListYearlyCalendars extends ListRecords
{
    protected static string $resource = YearlyCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
