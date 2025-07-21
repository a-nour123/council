<?php

namespace App\Filament\Resources\SubmitTopicResource\Widgets;

use App\Models\TopicAgenda;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgendasOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalAgendasCount = 0;
        $pendingAgendasCount = 0;
        $acceptedAgendasCount = 0;
        $rejectedAgendasCount = 0;

        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
            $totalAgendasCount = TopicAgenda::query()->count();
            $pendingAgendasCount = TopicAgenda::query()->where('status', operator: 0)->count();
            $acceptedAgendasCount = TopicAgenda::query()->where('status', 1)->count();
            $rejectedAgendasCount = TopicAgenda::query()->where('status', 2)->count();
        } else {
            $totalAgendasCount = TopicAgenda::query()->where('created_by', auth()->user()->id)->count();
            $pendingAgendasCount = TopicAgenda::query()->where('created_by', auth()->user()->id)->where('status', operator: 0)->count();
            $acceptedAgendasCount = TopicAgenda::query()->where('created_by', auth()->user()->id)->where('status', 1)->count();
            $rejectedAgendasCount = TopicAgenda::query()->where('created_by', auth()->user()->id)->where('status', 2)->count();
        }

        // Example trend data for weekly trends
        $weeklyTrend = fn() => array_map(fn() => rand(5, 20), range(1, 7));

        return [
            Stat::make('', $totalAgendasCount)
                ->description('مجموع الطلبات')
                ->descriptionIcon('heroicon-o-document-arrow-up')
                ->chart($weeklyTrend()) // Dynamic random data for total users
                ->color('primary'),

            Stat::make('', $pendingAgendasCount)
                ->description('مجموع الطلبات قيد الانتظار')
                ->descriptionIcon('heroicon-m-clock')
                ->chart($weeklyTrend()) // Dynamic random data for pending Agendas
                ->color('warning'),

            Stat::make('', $acceptedAgendasCount)
                ->description('مجموع الطلبات المقبولة')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($weeklyTrend()) // Dynamic random data for active Agendas
                ->color('success'),

            Stat::make('', $rejectedAgendasCount)
                ->description('مجموع الطلبات المرفوضة')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart($weeklyTrend()) // Dynamic random data for inactive Agendas
                ->color('danger'),
        ];
    }
}
