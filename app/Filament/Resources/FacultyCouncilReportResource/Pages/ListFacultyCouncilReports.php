<?php

namespace App\Filament\Resources\FacultyCouncilReportResource\Pages;

use App\Filament\Resources\FacultyCouncilReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFacultyCouncilReports extends ListRecords
{
    protected static string $resource = FacultyCouncilReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
