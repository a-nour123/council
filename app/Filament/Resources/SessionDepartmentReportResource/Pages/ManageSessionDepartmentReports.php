<?php

namespace App\Filament\Resources\SessionDepartmentReportResource\Pages;

use App\Filament\Resources\SessionDepartmentReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSessionDepartmentReports extends ManageRecords
{
    protected static string $resource = SessionDepartmentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
