<?php

namespace App\Filament\Resources\FacultyResource\Widgets;

use App\Models\Faculty;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FacultiesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalFacultiesCount = Faculty::query()->count();

        // Example trend data for weekly trends
        $weeklyTrend = fn() => array_map(fn() => rand(5, 20), range(1, 7));

        return [
            Stat::make('', $totalFacultiesCount)
                ->description('مجموع الكليات')
                ->descriptionIcon('heroicon-o-building-office')
                ->chart($weeklyTrend()) // Dynamic random data for total users
                ->color('primary'),

        ];
    }
}
