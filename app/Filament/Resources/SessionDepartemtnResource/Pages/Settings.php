<?php

namespace App\Filament\Resources\SessionDepartemtnResource\Pages;

use App\Filament\Resources\SessionDepartemtnResource;
use Filament\Resources\Pages\Page;

class Settings extends Page
{
    protected static string $resource = SessionDepartemtnResource::class;

    protected static string $view = 'filament.resources.session-departemtn-resource.pages.settings';
}
