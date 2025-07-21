<?php

namespace App\Filament\Resources\SessionDepartemtnResource\Widgets;

use App\Models\Department_Council;
use App\Models\Session;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SessionsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSessionsCount = 0;
        $acceptedSessionsCount = 0;
        $pendingSessionsCount = 0;
        $rejectedSessionsCount = 0;
        $userDepartmentCouncilIds = [];
        $attendedSessions = 0;
        $absentSessions = 0;
        $user = auth()->user();

        if ($user->hasRole('Super Admin') || $user->hasRole('System Admin')) {
            $totalSessionsCount = Session::query()->count();
            $pendingSessionsCount = Session::query()->where('status', 0)->count();
            $acceptedSessionsCount = Session::query()->where('status', 1)->count();
            $rejectedSessionsCount = Session::query()->where('status', 2)->count();
        }
        // If user position is head of department or secretary of department
        elseif ($user->position_id == 2 || $user->position_id == 3) {
            $userDepartmentCouncilIds = Department_Council::query()
                ->where('user_id', $user->id)
                ->pluck('department_id')
                ->unique();

            $totalSessionsCount = Session::query()
                ->whereIn('department_id', $userDepartmentCouncilIds)
                ->count();
            $acceptedSessionsCount = Session::query()
                ->whereIn('department_id', $userDepartmentCouncilIds)
                ->where('status', 1)
                ->count();
            $pendingSessionsCount = Session::query()
                ->whereIn('department_id', $userDepartmentCouncilIds)
                ->where('status', 0)
                ->count();
            $rejectedSessionsCount = Session::query()
                ->whereIn('department_id', $userDepartmentCouncilIds)
                ->where('status', '!=', 0)
                ->where('status', '!=', 1)
                ->count();
        } else {
            $userSessionIds = Session::where('status', 1)
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->orWhereHas('sessionEmails', function ($query) use ($user) {
                    $query->where('session_emails.user_id', $user->id);
                })
                ->pluck('id');

            $invitedSessions = DB::table('session_attendance_invites')
                ->whereIn('session_id', $userSessionIds)
                ->where('user_id', $user->id)
                ->get()
                ->unique();

            $attendedSessions = $invitedSessions->where('actual_status', 1)->count();
            $absentSessions = $invitedSessions->where('actual_status', '!=', 1)->count();
            $noActionSessions = count(array_diff($userSessionIds->toArray(), $invitedSessions->pluck('session_id')->toArray()));
        }

        // Example trend data for weekly trends
        $weeklyTrend = fn() => array_map(fn() => rand(5, 20), range(1, 7));

        // Build stats array based on roles/positions


        if ($user->hasRole('Super Admin') || $user->hasRole('System Admin') || $user->position_id == 2 || $user->position_id == 3) {
            $stats[] = Stat::make('', $totalSessionsCount)
                ->description('مجموع الجلسات')
                ->descriptionIcon('heroicon-o-chat-bubble-bottom-center')
                ->chart($weeklyTrend())
                ->color('primary');

            $stats[] = Stat::make('', $pendingSessionsCount)
                ->description('مجموع الجلسات قيد الانتظار')
                ->descriptionIcon('heroicon-m-clock')
                ->chart($weeklyTrend())
                ->color('warning');

            $stats[] = Stat::make('', $acceptedSessionsCount)
                ->description('مجموع الجلسات المقبولة')
                ->descriptionIcon('heroicon-o-check-circle')
                ->chart($weeklyTrend())
                ->color('success');

            $stats[] = Stat::make('', $rejectedSessionsCount)
                ->description('مجموع الجلسات المرفوضة')
                ->descriptionIcon('heroicon-o-x-circle')
                ->chart($weeklyTrend())
                ->color('danger');
        } else {
            $stats[] = Stat::make('', $userSessionIds->count())
                ->description('مجموع الجلسات')
                ->descriptionIcon('heroicon-o-chat-bubble-bottom-center')
                ->chart($weeklyTrend())
                ->color('primary');

            $stats[] = Stat::make('', $attendedSessions)
                ->description('مجموع جلسات الحضور')
                ->descriptionIcon('heroicon-o-check-circle')
                ->chart($weeklyTrend())
                ->color('success');

            $stats[] = Stat::make('', $absentSessions)
                ->description('مجموع جلسات الغياب')
                ->descriptionIcon('heroicon-o-x-circle')
                ->chart($weeklyTrend())
                ->color('danger');

            $stats[] = Stat::make('', $noActionSessions)
                ->description('مجموع جلسات بدون حالة')
                ->descriptionIcon('heroicon-m-clock')
                ->chart($weeklyTrend())
                ->color('warning');
        }

        return $stats;
    }
}
