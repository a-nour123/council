<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Widgets\UsersOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
            return [
                UsersOverview::class,
            ];
        }

        // Return an empty array if the condition is not met
        return [];
    }

}
