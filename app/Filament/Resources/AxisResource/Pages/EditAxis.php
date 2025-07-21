<?php

namespace App\Filament\Resources\AxisResource\Pages;

use App\Filament\Resources\AxisResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

class EditAxis extends EditRecord
{
    protected static string $resource = AxisResource::class;
    protected static string $view = 'filament.resources.axies.pages.edit';

    // public function render(): View
    // {
    //      return view('filament.resources.axies.pages.edit');
    // }

    // public function scripts()
    // {
    //     return [
    //         'https://code.jquery.com/jquery-3.6.0.min.js',
    //         'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js',
    //         asset('assets/form-builder/form-builder.min.js'),
    //         'https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js',
    //     ];
    // }
}


