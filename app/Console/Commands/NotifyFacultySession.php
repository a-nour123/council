<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\FacultySession;
use App\Models\FacultySessionEmail;
use App\Models\FacultySessionUser;
use App\Models\Session;
use App\Models\SessionEmail;
use App\Models\SessionUser;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationsAction;
use Carbon\Carbon;

class NotifyFacultySession extends Command
{
    protected $signature = 'notify:faculty-session';
    protected $description = 'Send a notification to invites users of faculty session for reminde of session time';

    public function handle()
    {
        // Assuming you have a way to retrieve the session data
        $sessions = FacultySession::get(); // Add your own condition here

        foreach ($sessions as $session) {
            $startTime = Carbon::parse($session->start_time);
            $startTimeString = $startTime->format('Y-m-d H');

            $nowTime = Carbon::parse(now());
            $nowTimeString = $nowTime->format('Y-m-d H');

            $notifiDateCarbon = $startTime->subHours(1);
            $notifiTimeString = $notifiDateCarbon->format('Y-m-d H');

            // dd($nowTimeString, $notifiTimeString);

            $sessionFacultyName = Faculty::where('id', $session->faculty_id)->value('ar_name');

            $sessionInvitations = FacultySessionUser::where('faculty_session_id', $session->id)->pluck('user_id')->toArray();
            $sessionEmailInvitations = FacultySessionEmail::where('faculty_session_id', $session->id)->pluck('user_id')->toArray();

            $sessionUsers = array_merge($sessionInvitations, $sessionEmailInvitations);
            $usersReciveNotification = User::whereIn('id', $sessionUsers)
                // ->whereNotIn('id', [$session->responsible_id, $session->created_by]) // don't take the head and secretary of department
                ->get();

            $appURL = env('APP_URL');

            // Build the URL dynamically
            $url = $appURL . '/admin/faculty-sessions/' . $session->id;

            if ($nowTimeString == $notifiTimeString) {
                // send notifications for invited users when the statuss is accepted
                Notification::make()
                    ->title('تذكير على موعد جلسة مجلس الكلية')
                    ->body('باقي من الزمن ساعة على موعد الجلسة كلية: ' . $sessionFacultyName . ' جلسة رقم: ' . $session->code)
                    ->actions([
                        NotificationsAction::make('view')
                            ->label('عرض الجلسة')
                            ->button()
                            ->url($url, shouldOpenInNewTab: true)
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($usersReciveNotification);
            }

        }
    }
}
