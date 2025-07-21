<?php

namespace App\Filament\Resources\FacultySessionResource\Pages;

use App\Filament\Resources\FacultySessionResource;
use App\Models\ControlReportFaculty;
use App\Models\CoverLetterReport;
use App\Models\Department;
use App\Models\FacultySession;
use App\Models\FacultySessionEmail;
use App\Models\Session;
use App\Models\SessionTopic;
use App\Models\SessionUser;
use App\Models\Topic;
use App\Models\TopicAgenda;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditFacultySession extends EditRecord
{
    protected static string $resource = FacultySessionResource::class;

    public function mount($record): void
    {

        // Check if the user is authorized to edit this record
        $session = FacultySession::find($record); // Assuming Session is your model
        // dd($session);

        $res = $session->responsible_id;
        $cre = $session->created_by;
        if (!$session) {
            abort(404, 'Session not found.');
        }
        if (((int) $session->status == 1 || (auth()->user()->id != $cre))) {
            abort(403, 'You do not have access to this page.');
        }


        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    public function mutateFormDataBeforeFill(array $data): array
    {
        $facultySessionId = $data['id'];

        $startDate = Carbon::parse($data['start_time']);

        // Cast total_hours to an integer
        $totalhours = intval($data['total_hours']);

        // Add the total hours to the start date
        $endDateCarbon = $startDate->addHours($totalhours);

        $dateString = $endDateCarbon->toDateTimeString();

        $data['scheduled_end_time'] = $dateString;

        // $agendaId = SessionTopic::where('session_id', $data['id'])->pluck('topic_agenda_id');

        $topicsFromFaculty = DB::table('faculty_session_topics')
            ->where('faculty_session_topics.faculty_session_id', $facultySessionId)
            ->join('topics_agendas as agendas', 'agendas.id', '=', 'faculty_session_topics.topic_agenda_id')
            ->where('agendas.classification_reference', 3)// don't call reference college
            ->pluck('faculty_session_topics.topic_agenda_id')->toArray();

        $agendaIds = DB::table('faculty_session_topics')
            ->where('faculty_session_topics.faculty_session_id', $facultySessionId)
            ->join('topics_agendas as agendas', 'agendas.id', '=', 'faculty_session_topics.topic_agenda_id')
            ->whereNot('agendas.classification_reference', 3)// don't call reference college
            ->pluck('faculty_session_topics.topic_agenda_id')->toArray();

        $data['TopicAgendaId'] = $agendaIds;
        $data['topicsFromFaculty'] = $topicsFromFaculty;

        // Fetch user IDs associated with the session
        $users = DB::table('faculty_session_users')->where('faculty_session_id', $facultySessionId)->pluck('user_id');

        // $topic_ids = TopicAgenda::whereIn('id', $agendaIds)->pluck('topic_id')->toArray();
        // $data['topic_id'] = $topic_ids;

        // $sessionDepartment = SessionTopic::whereIn('topic_agenda_id', $agendaIds)->value('session_id');
        $sessionDepartment = SessionTopic::whereIn('topic_agenda_id', $agendaIds)->pluck('session_id');
        $departments = Session::whereIn('id', $sessionDepartment)->pluck('department_id');

        $data['department_id'] = $departments;
        $data['session'] = $sessionDepartment;
        $data['currentSession'] = $sessionDepartment;

        // Fetch names and IDs of these users
        $invites = User::whereIn('id', $users)->pluck('name')->toArray();
        // Assign the name and ID pairs to the invitations key
        $data['invitations'] = $invites;

        /* $data['topic'] = $topTitle; */

        // Retrieve and return the TopicAgenda options
        /* $TopicAgendaId = TopicAgenda::whereIn('topic_id', $topicId)->pluck('id')->toArray(); */

        /*  $data['TopicAgendaId'] = $TopicAgendaId; */


        $sessionEmails = FacultySessionEmail::where('faculty_session_id', $data['id'])->get();

        $userEmailData = [];
        foreach ($sessionEmails as $sessionEmail) {
            $userEmailData[] = [
                'id' => $sessionEmail['id'],
                'name' => $sessionEmail['name'],
                'email' => $sessionEmail['email'],
                'user_id' => $sessionEmail['user_id'],
            ];
        }

        $data['email_invitations'] = $userEmailData;

        // dd($data);
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status'] = 0;
        $data['department_id'] = null;

        $facultySession = new FacultySession();

        $existSessionEmailIds = DB::table('faculty_session_emails')->where('faculty_session_id', $data['id'])->pluck('id')->toArray();

        $userIds = User::whereIn('name', $data['invitations'])->pluck('id')->toArray();

        $facultySession = $this->record; // Get the updated session record

        if (!empty($userIds)) {
            $facultySession->users()->sync($userIds);
        }

        $topicsFromFaculty = array_map('intval', $this->data['topicsFromFaculty']);
        $topicsFromDepartment = array_map('intval', $this->data['TopicAgendaId']); // Convert to integers

        $topicAgendaIds = array_merge($topicsFromFaculty, $topicsFromDepartment);

        // Fetch the topic IDs based on the provided TopicAgenda IDs
        $topicAgendas = TopicAgenda::whereIn('id', $topicAgendaIds)->get(['id', 'topic_id']);

        // Create a mapping of TopicAgenda IDs to topic IDs
        $topicIdMap = $topicAgendas->pluck('topic_id', 'id')->map(fn($id) => (int) $id)->toArray();

        // Fetch report content based on the topic IDs
        $reportContents = ControlReportFaculty::whereIn('topic_id', array_values($topicIdMap))
            ->pluck('content', 'topic_id')
            ->mapWithKeys(fn($content, $id) => [(int) $id => $content])
            ->toArray();

        $topicsFormate = ControlReportFaculty::whereIn('topic_id', array_values($topicIdMap))
            ->pluck('topic_formate', 'topic_id')
            ->mapWithKeys(fn($format, $id) => [(int) $id => $format])
            ->toArray();

        $coverLetterContents = CoverLetterReport::whereIn('topic_id', array_values($topicIdMap))
            ->pluck('content', 'topic_id')
            ->mapWithKeys(fn($content, $id) => [(int) $id => $content])
            ->toArray();

        DB::table('faculty_session_topics')->where('faculty_session_id', $facultySession->id)
            ->whereNotIn('topic_agenda_id', $topicAgendaIds)
            ->delete();

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
                ['faculty_session_id' => $facultySession->id, 'topic_agenda_id' => $agendaId], // Unique key for the record
                [
                    'report_template_content' => $reportContent,
                    'topic_formate' => $topicFormate,
                    'cover_letter_template_content' => $coverLetterContent, // Use specific content for each topic
                    'escalation_authority' => $agendaesAalationAuthority->escalation_authority,
                    'department_id'=>$departmentId,
                    'created_at' => now(),
                ] // Data to insert or update
            );
        }

        // if (!empty($this->data['TopicAgendaId'])) {
        //     $facultySession->topicAgenda()->sync($this->data['TopicAgendaId']);
        // }

        if (!empty($this->data['email invitations'])) {
            $emails = $this->data['email invitations'];
            $userEmailIds = [];

            foreach ($emails as $email) {
                if (isset($email['id'])) {
                    array_push($userEmailIds, $email['id']);

                    $exist_links = DB::table('faculty_session_emails')->find($email['id']);

                    if ($exist_links) {
                        DB::table('faculty_session_emails')
                            ->where('id', $email['id'])
                            ->update([
                                'name' => $email['name'],
                                'email' => $email['email'],
                                'updated_at' => now()
                            ]);
                    }
                } else {
                    DB::table('faculty_session_emails')->create([
                        'name' => $email['name'],
                        'email' => $email['email'],
                        'session_id' => $data['id'],
                        'created_at' => now(),
                        'updated_at' => null
                    ]);
                }
            }

            $not_exist_data = array_diff($existSessionEmailIds, $userEmailIds);

            foreach ($not_exist_data as $sessionData) {
                DB::table('faculty_session_emails')->where('id', $sessionData)->delete();
            }
        }

        return $data;
        // dd($data);
    }
}
