<?php

namespace App\Filament\Resources\SubmitTopicResource\Pages;

use App\Filament\Resources\SubmitTopicResource;
use App\Filament\Resources\SubmitTopicResource\Widgets\AgendasOverview;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListSubmitTopics extends ListRecords
{
    use ExposesTableToWidgets; // let the widget interact with table updates

    protected static string $resource = SubmitTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            AgendasOverview::class,
        ];
    }
}
