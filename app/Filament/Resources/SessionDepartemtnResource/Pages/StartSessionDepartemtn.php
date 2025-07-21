<?php

namespace App\Filament\Resources\SessionDepartemtnResource\Pages;

use App\Filament\Resources\SessionDepartemtnResource;
use App\Models\Session;
use App\Models\SessionAttendanceInvite;
use App\Models\SessionUser;
use App\Models\Topic;
use App\Models\User;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Illuminate\Contracts\Support\Htmlable;

class StartSessionDepartemtn extends Page
{
    protected static string $resource = SessionDepartemtnResource::class;

    protected static string $view = 'filament.resources.session-departemtn-resource.pages.session-users';

    public $record;
    public $users;
    public $userData;
    public $sessionDecisionApproval;
    public $decisionApproval;
    public function mount($record)
    {
        // Load the session record using the ID passed in the URL
        $this->record = Session::findOrFail($record);
        $decisions = $this->record->sessionDecisions;

        if ($this->record->actual_end_time != null) {
            abort(404,'Session has been ended');
        }

        if ($decisions->isNotEmpty()) {
            $firstDecision = $decisions->first();
            $this->sessionDecisionApproval = $firstDecision->approval;
        } else {
            // Handle case where there are no decisions, if necessary
            $this->sessionDecisionApproval = null; // or some default value
        }
        // Fetch users associated with the session
        $this->users = User::whereIn('id', function ($query) use ($record) {
            $query->select('user_id')
                ->from('session_user')
                ->where('session_id', $record)
                ->union(
                    \DB::table('session_emails')
                        ->select('user_id')
                        ->where('session_id', $record)
                );
        })->get();
        $authUserId = auth()->id();
        if (!$this->users->contains('id', $authUserId)) {
            abort(403, 'You do not have access to this session.'); // Abort with 403 Forbidden
        }
        // Fetch status and absent_reason from session_attendance_invites for each user
        foreach ($this->users as $user) {
            $attendanceInvite = SessionAttendanceInvite::where('user_id', $user->id)->first();
            if ($attendanceInvite) {
                $user->status = $attendanceInvite->status;
                $user->absent_reason = $attendanceInvite->absent_reason;
            } else {
                // Set default values if no invite found
                $user->status = null;
                $user->absent_reason = null;
            }
        }
        // Build array containing id, name, status, and absent_reason for each user
        $this->userData = $this->users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'status' => $user->status,
                'absent_reason' => $user->absent_reason
            ];
        })->toArray();
    }






    public function submitForm(array $data)
    {
        foreach ($data['users'] as $userData) {
            $userId = $userData['id'];
            $status = $userData['attendance'];
            $absentReason = $userData['absence_reason'] ?? null;

            SessionAttendanceInvite::updateOrCreate(
                [
                    'session_id' => $this->record->id,
                    'user_id' => $userId,
                ],
                [
                    'status' => $status,
                    'absent_reason' => $absentReason,
                ]
            );
        }

        $this->notify('success', 'Attendance records updated successfully.');
    }

    public function openModal()
    {
        $this->dispatchBrowserEvent('open-modal', [
            'form' => $this->getForm()->getState(),
        ]);
    }

    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
            'users' => $this->users,
            'userData' => $this->userData,
        ];
    }
    public function getTitle(): string|Htmlable
    {
        return __('Start Department Council Session');
    }
}
