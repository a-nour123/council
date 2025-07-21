<?php

namespace App\Filament\Resources\YearlyCalendarResource\Pages;

use App\Filament\Resources\YearlyCalendarResource;
use App\Models\YearlyCalendar;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditYearlyCalendar extends EditRecord
{
    protected static string $resource = YearlyCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['status'] == 1) {
            YearlyCalendar::where('status', 1)->update([
                'status' => 0,
            ]);
        }
        return $data;
    }
}
