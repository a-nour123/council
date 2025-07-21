<?php

namespace App\Filament\Resources\AxisResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\AxisResource;

class CreateAxis extends CreateRecord
{
    protected static string $resource = AxisResource::class;
      protected static string $view = 'filament.resources.axies.pages.createaxies';

}
