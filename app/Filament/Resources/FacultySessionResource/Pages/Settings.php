<?php

namespace App\Filament\Resources\FacultySessionResource\Pages;

use App\Filament\Resources\FacultySessionResource;
use Filament\Resources\Pages\Page;

class Settings extends Page
{
    protected static string $resource = FacultySessionResource::class;

    protected static string $view = 'filament.resources.faculty-session-resource.pages.settings';
}
