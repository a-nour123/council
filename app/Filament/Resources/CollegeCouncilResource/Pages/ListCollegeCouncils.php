<?php

namespace App\Filament\Resources\CollegeCouncilResource\Pages;

use App\Filament\Resources\CollegeCouncilResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollegeCouncils extends ListRecords
{
    protected static string $resource = CollegeCouncilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
