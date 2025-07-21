<?php

namespace App\Filament\Resources\AxisResource\Pages;

use App\Filament\Resources\AxisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAxes extends ListRecords
{
    protected static string $resource = AxisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
