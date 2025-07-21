<?php

namespace App\Filament\Resources\TopicResource\Widgets;

use App\Models\Topic;
use Filament\Widgets\ChartWidget;

class TopicsChartsOverview extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $mainTopicsCount = Topic::query()->where('classification_reference', 1)->count();
        $departmentRefTopicsCount = Topic::query()->where('classification_reference', 2)->count();
        $facultyRefTopicsCount = Topic::query()->where('classification_reference', 3)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Topics Count',
                    'data' => [
                        $mainTopicsCount,
                        $departmentRefTopicsCount,
                        $facultyRefTopicsCount,
                    ],
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.8)', // Darker teal
                        'rgba(255, 159, 64, 0.8)', // Darker orange
                        'rgba(54, 162, 235, 0.8)', // Darker blue
                    ],
                    'borderColor' => [
                        'rgba(75, 192, 192, 1)', // Teal
                        'rgba(255, 159, 64, 1)', // Orange
                        'rgba(54, 162, 235, 1)', // Blue
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['مرجع تصنيف مشترك', 'مرجع تصنيف قسم', 'مرجع تصنيف كلية'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '70%', // Adjust the size of the inner radius
            'plugins' => [
                'legend' => [
                    'position' => 'bottom', // Optional: Adjust legend position
                ],
            ],
            'scales' => [ // Configure axes to remove lines and numbers
                'x' => [
                    'grid' => [
                        'display' => false, // Remove x-axis grid lines
                    ],
                    'ticks' => [
                        'display' => false, // Remove x-axis numbers
                    ],
                ],
                'y' => [
                    'grid' => [
                        'display' => false, // Remove y-axis grid lines
                    ],
                    'ticks' => [
                        'display' => false, // Remove y-axis numbers
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'aspectRatio' => 1.4, // Controls the width-to-height ratio
        ];
    }


    protected static function getColumns(): int|array
    {
        return 1; // Full width
    }
}
