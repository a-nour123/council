<?php

namespace App\Filament\Resources\SessionDepartemtnResource\Pages;

use App\Filament\Resources\SessionDepartemtnResource;
use App\Models\ControlReport;
use App\Models\CoverLetterReport;
use App\Models\Department;
use App\Models\Department_Council;
use App\Models\Session;
use App\Models\SessionEmail;
use App\Models\SessionUser;
use App\Models\TopicAgenda;
use App\Models\User;
use App\Models\YearlyCalendar;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Actions\Action as NotificationsAction;

class CreateSessionDepartemtn extends CreateRecord
{
    protected static string $resource = SessionDepartemtnResource::class;
    public function mount(): void
    {
        // Check if the user is authorized to access this page
        if (auth()->user()->position_id = 2) {
            parent::mount();
        } else {
            // Throw an authorization exception
            abort(403, 'You do not have access to this page.');
        }
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // calling session model
        $session = new Session();
        // Access the first TopicAgendaId
        if (isset($data['TopicAgendaId'][0])) {
            $topicAgendaId = $data['TopicAgendaId'][0];

            // Retrieve the TopicAgenda entry using the ID
            $topicAgenda = TopicAgenda::find($topicAgendaId);

            if ($topicAgenda) {
                // Update the data array with the faculty_id from TopicAgenda
                $data['faculty_id'] = $topicAgenda->faculty_id;
            }
        }

        if (isset($data['department_id']) && !empty($data['department_id'])) {
            $data['department_id'] = (int) $data['department_id'];
        } else {
            $data['department_id'] = Department_Council::where('user_id', auth()->user()->id)->value('department_id');
        }
        $departmentId = $data['department_id'];

        $GetDep = Department_Council::where('department_id', $departmentId)->where('position_id', 3)->value('user_id');

        // let responsible_id = createdById from topicAgenda
        $data['responsible_id'] = $GetDep;
        // Parse the start time using Carbon
        $startDate = Carbon::parse($data['start_time']);

        // Cast total_hours to an integer
        $totalhours = intval($data['total_hours']);

        // Add the total hours to the start date
        $endDateCarbon = $startDate->addHours($totalhours);

        $dateString = $endDateCarbon->toDateTimeString();

        $data['scheduled_end_time'] = $dateString;

        $newNumber = rand(100, 999);

        // return code of active year
        $activeYear = YearlyCalendar::where('status', 1)->select('id', 'code')->first();

        $departmentCode = Department::where('id', $data['department_id'])->value('code');

        // Generate the new code
        $newCode = $activeYear->code . '_' . $departmentCode . '_' . $newNumber;
        $data['code'] = $newCode;
        $data['yearly_calendar_id'] = $activeYear->id;

        $data['decision_by'] = (int) $data['decision_by'];




        $sessionLastOrder = (int) Session::where('department_id', $data['department_id'])
            ->orderBy('order', 'desc')
            ->pluck('order')
            ->first() ?? 1;

        $data['order'] = $sessionLastOrder += 1;

        return $data;
    }

    protected function afterCreate(): void
    {
        $session = $this->record; // Get the created session record
        // dd($session->responsible_id);
        $sessionEmail = new SessionEmail();
        // Fetch the IDs from the Department_Council model
        $mainInvitesSecAndhead = Department_Council::where('department_id', $session->department_id)
            ->where(function ($query) {
                $query->where('position_id', 3)
                    ->orWhere('position_id', 2);
            })
            ->pluck('user_id')
            ->toArray(); // Convert collection to array

        // Merge the arrays and remove duplicates
        $this->data['invitations'] = array_unique(array_merge($this->data['invitations'], $mainInvitesSecAndhead));
        // Attach invitations
        if (!empty($this->data['invitations'])) {
            $session->users()->attach($this->data['invitations']);
        }

        $topicAgendaIds = $this->data['TopicAgendaId']; // Topic Agenda IDs from the input

        // Fetch the topic IDs based on the provided TopicAgenda IDs
        $topicAgendas = TopicAgenda::whereIn('id', $topicAgendaIds)->get(['id', 'topic_id']);

        // Create a mapping of TopicAgenda IDs to topic IDs
        $topicIdMap = $topicAgendas->pluck('topic_id', 'id')->toArray();

        // Fetch report content based on the topic IDs
        $reportContents = ControlReport::whereIn('topic_id', array_values($topicIdMap))->pluck('content', 'topic_id')->toArray();
        $topicsFormate = ControlReport::whereIn('topic_id', array_values($topicIdMap))->pluck('topic_formate', 'topic_id')->toArray();
        $coverLetterContents = CoverLetterReport::whereIn('topic_id', array_values($topicIdMap))->pluck('content', 'topic_id')->toArray();

        // Loop through each TopicAgenda ID and update or insert into the pivot table
        foreach ($topicAgendaIds as $agendaId) {
            $topicId = $topicIdMap[$agendaId] ?? null; // Get the topic_id based on the TopicAgenda ID
            $reportContent = $reportContents[$topicId] ?? null; // Get report content or null if not found
            $topicFormate = $topicsFormate[$topicId] ?? null; // Get topic formate or null if not found
            $coverLetterContent = $coverLetterContents[$topicId] ?? null; // Get cover letter content or null if not found
            $agendaesAalationAuthority=TopicAgenda::where('id',$agendaId)->latest()->first();

            // Insert or update the pivot table
            DB::table('session_topics')->updateOrInsert(
                [
                    'session_id' => $session->id,
                    'topic_agenda_id' => $agendaId,
                 ],
                [
                    'topic_formate' => $topicFormate,
                    'report_template_content' => $reportContent,
                    'cover_letter_template_content' => $coverLetterContent, // Use specific content for each topic
                    'escalation_authority'=>$agendaesAalationAuthority->escalation_authority,
                    'created_at' => now()
                ]
            );
        }

        if (!empty($this->data['email_invitations'])) {
            $emails = $this->data['email_invitations'];
            $userEmailData = [];
            $userEmailIds = [];

            foreach ($emails as $email) {
                // Check if the email exists in the User table
                $userId = User::where('email', $email['email'])->value('id');

                if ($userId) {
                    if (isset($email['id'])) {
                        array_push($userEmailIds, $email['id']);
                    }

                    // Add email to $userEmailData for insertion
                    $userEmailData[] = [
                        'name' => $email['name'],
                        'email' => $email['email'],
                        'session_id' => $session->id,
                        'user_id' => $userId,
                        'created_at' => now(),
                        'updated_at' => null
                    ];
                } else {
                    // Optionally handle or log cases where the email does not exist in User table
                    // Example: Log::warning('Email '.$email['email'].' does not exist in User table.');
                }
            }

            // Insert session email records if there are any to insert
            if (!empty($userEmailData)) {
                $sessionEmail->insert($userEmailData);
            }
        }

        $sessionDepartmentName = Department::where('id', $session->department_id)->value('ar_name');
        $appURL = env('APP_URL');
        // Build the URL dynamically
        $url = $appURL . '/admin/session-departemtns/' . $session->id;

        // sending notification to the head of department
        Notification::make()
            ->title('تم إنشاء جلسة مجلس قسم جديدة')
            ->body('قسم' . ': ' . $sessionDepartmentName . ' جلسة رقم: ' . $session->code)
            ->actions([
                NotificationsAction::make('view')
                    ->label('عرض الجلسة')
                    ->button()
                    ->url($url, shouldOpenInNewTab: true)
                    ->markAsRead(),
            ])
            ->sendToDatabase(User::where('id', $session->responsible_id)->get());
    }
}
