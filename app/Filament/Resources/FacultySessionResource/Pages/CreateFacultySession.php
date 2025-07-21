<?php

namespace App\Filament\Resources\FacultySessionResource\Pages;

use App\Filament\Resources\FacultySessionResource;
use App\Models\CollegeCouncil;
use App\Models\ControlReport;
use App\Models\ControlReportFaculty;
use App\Models\CoverLetterReport;
use App\Models\Department;
use App\Models\Department_Council;
use App\Models\Faculty;
use App\Models\FacultyCouncil;
use App\Models\FacultySession;
use App\Models\FacultySessionEmail;
use App\Models\Session;
use App\Models\TopicAgenda;
use App\Models\User;
use App\Models\YearlyCalendar;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationsAction;
use Illuminate\Support\Facades\DB;

class CreateFacultySession extends CreateRecord
{
    protected static string $resource = FacultySessionResource::class;

    public function mount(): void
    {
        // Check if the user is authorized to access this page
        if (auth()->user()->position_id != 4) {
            // Throw an authorization exception
            abort(403, 'You do not have access to this page.');
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // $sessionId = $data['session'];

        // $sessionData = Session::where('id', $sessionId)->get()->toArray();
        // $departmentId = array_column($sessionData, 'department_id')[0];

        // $facultyId = Department::where('id', $departmentId)->value('faculty_id');
        // $data['faculty_id'] = $facultyId;
        $facultyId = $data['faculty_id'];

        // $facultyId = auth()->user()->faculty_id;
        // dd($facultyId);
        $responsible_id = FacultyCouncil::where('faculty_id', $facultyId)->where('position_id', 5)->value('user_id'); // dean of collage
        $data['responsible_id'] = $responsible_id;

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

        $facultyCode = Faculty::where('id', $facultyId)->value('code');

        // Generate the new code
        $newCode = $activeYear->code . '_' . $facultyCode . '_' . $newNumber;

        $data['code'] = $newCode;
        $data['decision_by'] = (int) $data['decision_by'];
        $data['yearly_calendar_id'] = $activeYear->id;

        // $agendaIds =  TopicAgenda::whereIn('topic_id', $data['topic_id'])->pluck('id')->toArray();
        // $data['TopicAgendaId'] = $agendaIds;

        $sessionLastOrder = (int) FacultySession::where('faculty_id', $facultyId)
            ->orderBy('order', 'desc')
            ->pluck('order')
            ->first() ?? 1;

        $data['order'] = $sessionLastOrder += 1;
        $data['department_id'] = null;
        // dd($data);
        return $data;
    }

    protected function afterCreate(): void
    {
        $facultySession = $this->record; // Get the created faculty session record
        $sessionEmail = new FacultySessionEmail();

        // Fetch the IDs from the Department_Council model
        $mainInvitesSecAndhead = FacultyCouncil::where('faculty_id', $facultySession->faculty_id)
            ->where(function ($query) {
                $query->where('position_id', 4);
            })
            ->pluck('user_id')
            ->toArray(); // Convert collection to array

        // Merge the arrays and remove duplicates
        $this->data['invitations'] = array_unique(array_merge($this->data['invitations'], $mainInvitesSecAndhead));

        // Attach invitations
        if (!empty($this->data['invitations'])) {
            $facultySession->users()->attach($this->data['invitations']);
        }

        $topicsFromFaculty = $this->data['topicsFromFaculty'];

        $topicsFromDepartment = $this->data['TopicAgendaId']; // Topic Agenda IDs from the input

        $topicAgendaIds = array_merge($topicsFromFaculty, $topicsFromDepartment);

        // Fetch the topic IDs based on the provided TopicAgenda IDs
        $topicAgendas = TopicAgenda::whereIn('id', $topicAgendaIds)->get(['id', 'topic_id']);

        // Create a mapping of TopicAgenda IDs to topic IDs
        $topicIdMap = $topicAgendas->pluck('topic_id', 'id')->toArray();

        // Fetch report content based on the topic IDs
        $reportContents = ControlReportFaculty::whereIn('topic_id', array_values($topicIdMap))->pluck('content', 'topic_id')->toArray();
        $topicsFormate = ControlReportFaculty::whereIn('topic_id', array_values($topicIdMap))->pluck('topic_formate', 'topic_id')->toArray();
        $coverLetterContents = CoverLetterReport::whereIn('topic_id', array_values($topicIdMap))->pluck('content', 'topic_id')->toArray();

        // Loop through each TopicAgenda ID and update or insert into the pivot table
        foreach ($topicAgendaIds as $agendaId) {
            $topicId = $topicIdMap[$agendaId] ?? null; // Get the topic_id based on the TopicAgenda ID
            $reportContent = $reportContents[$topicId] ?? null; // Get report content or null if not found
            $topicFormate = $topicsFormate[$topicId] ?? null; // Get topic formate or null if not found
            $coverLetterContent = $coverLetterContents[$topicId] ?? null; // Get cover letter content or null if not found
            $agendaesAalationAuthority = TopicAgenda::where('id', $agendaId)->latest()->first();

            $departmentId=TopicAgenda::where('id',$agendaId)->value('department_id');

            // Insert or update the pivot table
            DB::table('faculty_session_topics')->updateOrInsert(
                ['faculty_session_id' => $facultySession->id, 'topic_agenda_id' => $agendaId, 'created_at' => now()], // Unique key for the record
                [
                    'report_template_content' => $reportContent,
                    'topic_formate' => $topicFormate,
                    'cover_letter_template_content' => $coverLetterContent, // Use specific content for each topic
                    'escalation_authority' => $agendaesAalationAuthority->escalation_authority,
                    'department_id'=>$departmentId
                ] // Data to insert or update
            );
        }

        // // Attach topic agendas
        // if (!empty($this->data['TopicAgendaId'])) {
        //     $facultySession->topicAgenda()->attach($this->data['TopicAgendaId']);
        // }
        // if (!empty($this->data['email_invitations'])) {
        //     // Assuming 'email invitations' is an array of emails
        //     $emails = $this->data['email_invitations'];

        //     foreach ($emails as $email) {

        //         // Check if the email exists in the User table
        //         $userId = User::where('email', $email['email'])->value('id');

        //         $userEmailData[] = [
        //             'name' => $email['name'],
        //             'email' => $email['email'],
        //             'faculty_session_id' => $facultySession->id,
        //             'user_id' => $userId,
        //             'created_at' => now(),
        //             'updated_at' => null
        //         ];
        //     }
        //     if (!empty($userEmailData)) {
        //         $sessionEmail->insert($userEmailData);
        //     }
        // }

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

        $sessionFacultyName = Faculty::where('id', $facultySession->faculty_id)->value('ar_name');
        $appURL = env('APP_URL');
        // Build the URL dynamically
        $url = $appURL . '/admin/faculty-sessions/' . $facultySession->id;

        // sending notification to the head of department
        Notification::make()
            ->title('تم إنشاء جلسة مجلس كلية جديدة')
            ->body('كلية' . ': ' . $sessionFacultyName . ' جلسة رقم: ' . $facultySession->code)
            ->actions([
                NotificationsAction::make('view')
                    ->label('عرض الجلسة')
                    ->button()
                    ->url($url, shouldOpenInNewTab: true)
                    ->markAsRead(),
            ])
            ->sendToDatabase(User::where('id', $facultySession->responsible_id)->get());
    }
}
