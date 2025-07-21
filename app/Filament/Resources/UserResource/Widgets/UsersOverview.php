<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UsersOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsersCount = User::query()->count();
        $activeUsersCount = User::query()->where('is_active', 1)->count();
        $inactiveUsersCount = User::query()->where('is_active', 0)->count();
        $pendingUsersCount = User::query()->where('is_active', 2)->count();

        // Example trend data for weekly trends
        $weeklyTrend = fn() => array_map(fn() => rand(5, 20), range(1, 7));

        return [
            Stat::make('', $totalUsersCount)
                ->description('مجموع المستخدمين')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($weeklyTrend()) // Dynamic random data for total users
                ->color('primary'),

            Stat::make('', $pendingUsersCount)
                ->description('مجموع المستخدمين قيد القبول')
                ->descriptionIcon('heroicon-m-clock')
                ->chart($weeklyTrend()) // Dynamic random data for pending users
                ->color('warning'),

            Stat::make('', $activeUsersCount)
                ->description('مجموع المستخدمين النشطيين')
                ->descriptionIcon('heroicon-o-check-circle')
                ->chart($weeklyTrend()) // Dynamic random data for active users
                ->color('success'),

            Stat::make('', $inactiveUsersCount)
                ->description('مجموع المستخدمين غير النشطيين')
                ->descriptionIcon('heroicon-o-x-circle')
                ->chart($weeklyTrend()) // Dynamic random data for inactive users
                ->color('danger'),
        ];
    }
}
