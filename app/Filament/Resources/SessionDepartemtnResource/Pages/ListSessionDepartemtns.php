<?php

namespace App\Filament\Resources\SessionDepartemtnResource\Pages;

use App\Filament\Resources\SessionDepartemtnResource;
use App\Filament\Resources\SessionDepartemtnResource\Widgets\SessionsChartsOverview;
use App\Filament\Resources\SessionDepartemtnResource\Widgets\SessionsOverview;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListSessionDepartemtns extends ListRecords
{
    use ExposesTableToWidgets; // let the widget interact with table updates

    protected static string $resource = SessionDepartemtnResource::class;

    protected function getHeaderActions(): array
    {
        if (auth()->user()->position_id == 2) {
            return [
                Actions\CreateAction::make(),
            ];
        }

        return []; // Return an empty array if the condition is not met
    }
    protected function getHeaderWidgets(): array
    {
        $user = auth()->user();

        $widgets = [
            SessionsOverview::class,
        ];

        // display just for super & system admins and users with position head & secrtary of department
        if ($user->hasRole('Super Admin') || $user->hasRole('System Admin') || $user->position_id == 3 || $user->position_id == 2) {
            $widgets[] = SessionsChartsOverview::class;
        }

        return $widgets;
    }
}
