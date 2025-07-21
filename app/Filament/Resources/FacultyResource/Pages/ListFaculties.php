<?php

namespace App\Filament\Resources\FacultyResource\Pages;

use App\Filament\Resources\FacultyResource;
use App\Filament\Resources\FacultyResource\Widgets\FacultiesOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListFaculties extends ListRecords
{
    use ExposesTableToWidgets; // let the widget interact with table updates

    protected static string $resource = FacultyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
            return [
                FacultiesOverview::class,
            ];
        }

        // Return an empty array if the condition is not met
        return [];
    }
}
