<?php

namespace App\Filament\Resources\HeadquarterResource\Pages;

use App\Filament\Resources\HeadquarterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHeadquarters extends ListRecords
{
    protected static string $resource = HeadquarterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
