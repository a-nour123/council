<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        // $user = auth()->user();
        $user = Auth::user();

        if ($user->is_active === 0) {
            Notification::make()
                ->title(__('Your account has been disabled, please contact the administration'))
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->color('danger')
                ->send();

            $appURL = env('APP_URL');

            // Build the URL dynamically
            $url = $appURL . '/admin/login';

            return redirect()->away($url);
        } elseif ($user->is_active === 2) {
            Notification::make()
                ->title(__('Your account has been pending to accept from Head of Department'))
                ->icon('heroicon-o-x-circle')
                ->info()
                ->color('info')
                ->send();
            $appURL = env('APP_URL');

            // Build the URL dynamically
            $url = $appURL . '/admin/login';

            return redirect()->away($url);
        }

        return $next($request);
    }
}
