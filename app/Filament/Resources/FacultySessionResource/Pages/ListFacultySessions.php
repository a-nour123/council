<?php

namespace App\Filament\Resources\FacultySessionResource\Pages;

use App\Filament\Resources\FacultySessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFacultySessions extends ListRecords
{
    protected static string $resource = FacultySessionResource::class;

    protected function getHeaderActions(): array
    {
        if (auth()->user()->position_id == 4) {
            return [
                Actions\CreateAction::make(),
            ];
        }

        return []; // Return an empty array if the condition is not met
    }
}
