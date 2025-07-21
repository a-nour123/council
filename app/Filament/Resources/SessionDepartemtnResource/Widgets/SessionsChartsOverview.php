<?php

namespace App\Filament\Resources\SessionDepartemtnResource\Widgets;

use App\Models\Department;
use App\Models\Session;
use App\Models\SessionDecision;
use App\Models\Department_Council;
use App\Models\Faculty;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\ChartWidget;

class SessionsChartsOverview extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static bool $isLazy = true;
    public ?string $filter = '0';
    protected function getData(): array
    {
        $user = auth()->user();
        $approvedSesisonIds = 0;
        $rejectedSesisonIds = 0;
        $sessionsWithoutDecision = 0;
        $departmentFilter = $this->filter ?? 0; // Get selected department filter value

        if ($user->hasRole('Super Admin') || $user->hasRole('System Admin')) {
            if ($departmentFilter != 0) {
                $allSessionIds = Session::query()->where('department_id', $departmentFilter)->pluck('id');
                $approvedSesisonIds = SessionDecision::query()->where('approval', 1)->pluck('session_id')->unique();
                $rejectedSesisonIds = SessionDecision::query()->where('approval', 2)->pluck('session_id')->unique();
            } else {
                $allSessionIds = Session::query()->pluck('id');
                $approvedSesisonIds = SessionDecision::query()->where('approval', 1)->pluck('session_id')->unique();
                $rejectedSesisonIds = SessionDecision::query()->where('approval', 2)->pluck('session_id')->unique();
            }

            $sessionsWithoutDecision = $allSessionIds->count() - ($approvedSesisonIds->count() + $rejectedSesisonIds->count());
        }
        // If user position is head of department or secretary of department
        else if ($user->position_id == 2 || $user->position_id == 3) {
            $userDepartmentCouncilIds = Department_Council::query()
                ->where('user_id', $user->id)
                ->pluck('department_id')
                ->unique();

            $allSessionIds = Session::query()
                ->whereIn('department_id', $userDepartmentCouncilIds)
                ->pluck('id');

            $approvedSesisonIds = SessionDecision::query()->where('approval', 1)->pluck('session_id')->unique();
            $rejectedSesisonIds = SessionDecision::query()->where('approval', 2)->pluck('session_id')->unique();

            $sessionsWithoutDecision = $allSessionIds->count() - ($approvedSesisonIds->count() + $rejectedSesisonIds->count());
        }

        // dd([
        //     'allSessionIds' => $allSessionIds->count(),
        //     'approvedSesisonIds' => $approvedSesisonIds->count(),
        //     'rejectedSesisonIds' => $rejectedSesisonIds->count(),
        //     'sessionsWithoutDecision' => $sessionsWithoutDecision,
        // ]);

        return [
            'datasets' => [
                [
                    'label' => '',
                    'data' => [
                        $approvedSesisonIds->count(),
                        $rejectedSesisonIds->count(),
                        $sessionsWithoutDecision
                    ],
                    'backgroundColor' => [
                        'rgba(40, 167, 69, 0.8)', // success (green)
                        'rgba(220, 53, 69, 0.8)', // danger (red)
                        'rgba(255, 193, 7, 0.8)', // warning (yellow)
                    ],
                    'borderColor' => [
                        'rgba(40, 167, 69, 1)', // success (green)
                        'rgba(220, 53, 69, 1)', // danger (red)
                        'rgba(255, 193, 7, 1)', // warning (yellow)
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['الجلسات المعتمدة', 'الجلسات المرفوضة', 'الجلسات قيد الاعتماد'],
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

    protected function getFilters(): ?array
    {
        $user = auth()->user();

        // Get the departments related to the sessions
        $departments = Session::query()
            ->with('department')  // Load the department relationship
            ->get()  // Retrieve the sessions
            ->pluck('department.ar_name', 'department.id')  // Pluck 'ar_name' and 'id' from the department relation
            ->unique()  // Remove duplicates
            ->toArray();  // Convert to array

        // Add the "Select department" option at the beginning
        $departments = [0 => __('Select department')] + $departments;

        // Return the departments if the user has the required roles
        return $user && ($user->hasRole('Super Admin') || $user->hasRole('System Admin')) ? $departments : null;
    }


}
