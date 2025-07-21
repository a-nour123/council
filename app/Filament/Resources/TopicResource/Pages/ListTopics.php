<?php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Resources\TopicResource;
use App\Filament\Resources\TopicResource\Widgets\TopicsChartsOverview;
use App\Filament\Resources\TopicResource\Widgets\TopicsOverview;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListTopics extends ListRecords
{
    use ExposesTableToWidgets; // let the widget interact with table updates

    protected static string $resource = TopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            TopicsOverview::class,
            TopicsChartsOverview::class,
        ];
    }
}
