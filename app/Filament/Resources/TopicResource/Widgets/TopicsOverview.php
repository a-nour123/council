<?php

namespace App\Filament\Resources\TopicResource\Widgets;

use App\Models\Topic;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TopicsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTopicsCount = Topic::query()->count();
        $mainTopicsCount = Topic::query()->whereNull('main_topic_id')->count();
        $subTopicsCount = Topic::query()->whereNotNull('main_topic_id')->count();

        // Example trend data for weekly trends
        $weeklyTrend = fn() => array_map(fn() => rand(5, 20), range(1, 7));

        return [
            Stat::make('', $totalTopicsCount)
                ->description('مجموع التصنيفات')
                ->descriptionIcon('heroicon-o-document-text')
                ->chart($weeklyTrend()) // Dynamic random data for total users
                ->color('primary'),

            Stat::make('', $mainTopicsCount)
                ->description('مجموع التصنيفات الرئيسية')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart($weeklyTrend()) // Dynamic random data for pending Agendas
                ->color('warning'),

            Stat::make('', $subTopicsCount)
                ->description('مجموع التصنيفات الفرعية')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($weeklyTrend()) // Dynamic random data for active Agendas
                ->color('success'),
        ];
    }
}
