<?php

namespace App\Filament\Resources\FacultySessionResource\Pages;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Filament\Resources\FacultySessionResource;
use App\Filament\Resources\SessionDepartemtnResource;
use App\Models\Session;
use App\Models\SessionTopic;
use App\Models\TopicAgenda;
use App\Models\Department;
use App\Models\Department_Council;
use App\Models\Faculty;
use App\Models\FacultySession;
use App\Models\FacultySessionAttendanceInvite;
use App\Models\FacultySessionDecision;
use App\Models\FacultySessionEmail;
use App\Models\FacultySessionTopic;
use App\Models\FacultySessionUser;
use App\Models\Position;
use App\Models\SessionAttendanceInvite;
use App\Models\SessionDecision;
use App\Models\SessionEmail;
use App\Models\SessionUser;
use App\Models\Topic;
use App\Models\User;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class FacultyAttandenceList extends Page
{
    protected static string $resource = FacultySessionResource::class;
    protected static string $view = 'filament.resources.faculty-session-resource.pages.AttandenceList';

    public $recordId;
    public $TopicTitle;
    public $SessionCode;
    public $SessionDep;
    public $depName;
    public $facName;
    public $startTime;
    public $facDeanName;
    public $SessionPlace;
    public $startDate;
    public $higriDate;
    public $dayName;
    public $users;
    public $members = [];
    public $invitedMembers = [];
    public $topics = [];
    public $decisions = [];
    public $decisionApproval = [];
    public $sessionResposibleId;
    public $depNameEn, $facNameEn;
    public function mount($recordId)
    {
        // Check if the user is authorized to edit this record
        $session = FacultySession::findOrFail($recordId);
        $res = $session->responsible_id;
        $cre = $session->created_by;
        $sessionInvites = FacultySessionUser::where('faculty_session_id', $recordId)->pluck('user_id')->toArray();
        $sessionEmailsInvites = FacultySessionEmail::where('faculty_session_id', $recordId)->pluck('user_id')->toArray();
        if (!$session) {
            abort(404, 'Session not found.');
        }
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('System Admin') && auth()->user()->id != $cre && auth()->user()->id != $res && !in_array(auth()->user()->id, $sessionInvites) && !in_array(auth()->user()->id, $sessionEmailsInvites)) {
            // Throw an authorization exception
            abort(403, 'You do not have access to this page.');
        }

        $this->recordId = $recordId;

        $session = FacultySession::findOrFail($recordId);
        $sessionTopic = FacultySessionTopic::where('faculty_session_id', $recordId)->firstOrFail();
        $topicAgenda = TopicAgenda::findOrFail($sessionTopic->topic_agenda_id);
        $department = Department::findOrFail($topicAgenda->department_id);
        $faculty = Faculty::findOrFail($topicAgenda->faculty_id);

        // $depHead = Department_Council::where('department_id', $department->id)->where('position_id', 3)->firstOrFail();
        $facDeanName = User::findOrFail($session->responsible_id);

        $startDateTime = Carbon::parse($session->start_time);

        $this->sessionResposibleId = $session->responsible_id;
        $this->SessionCode = $session->code;
        $this->SessionDep = $sessionTopic->topic_agenda_id;
        $this->depName = $department->ar_name;
        $this->depNameEn = $department->en_name;
        $this->facName = $faculty->ar_name;
        $this->facNameEn = $faculty->en_name;
        $this->facDeanName = $facDeanName->name;
        $this->SessionPlace = $session->place;
        $this->startTime = $startDateTime->format('g'); // 12-hour format without leading zero
        $this->startDate = $startDateTime->format('Y:m:d'); // Date format: Year:Month:Day
        $this->higriDate = Hijri::DateIndicDigits('Y-m-d', $startDateTime->format('Y-m-d'));
        $this->dayName = Hijri::DateIndicDigits('l', $startDateTime->format('l'));

        // Assuming $session and $recordId are defined
        $sessionEmailsUser = FacultySessionEmail::where('faculty_session_id', $session->id)->pluck('user_id')->toArray();
        $sessionUserIds = FacultySessionUser::where('faculty_session_id', $session->id)->pluck('user_id')->toArray();

        $users = array_merge($sessionUserIds, $sessionEmailsUser);

        // Get users with positions 1, 2, 3, 4, 5
        $usersJob = User::whereIn('id', $users)
            ->whereIn('position_id', [1, 2, 3, 4, 5])
            ->pluck('position_id', 'id');

        // Get position titles
        $positions = Position::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->pluck('name', 'id');
        $positionsName = [
            1 => ['en' => 'Academic Staff', 'ar' => 'عضو هيئة تدريس'],
            2 => ['en' => 'Secretary of the Department Council', 'ar' => 'أمين مجلس القسم'],
            3 => ['en' => 'Head of Department', 'ar' => 'رئيس القسم'],
            4 => ['en' => 'Secretary of the College Council', 'ar' => 'أمين مجلس الكلية'],
            5 => ['en' => 'Dean of the College', 'ar' => 'عميد الكلية'],
            6 => ['en' => 'Vice Rector for Educational Affairs', 'ar' => 'نائب رئيس الجامعة للشؤون التعليمية'],
            7 => ['en' => 'Prex', 'ar' => 'رئيس'],
        ];

        // Separate users into members and invited members
        $this->members = [];
        $this->invitedMembers = [];

        // Define the translation array
        $attendanceStatuses = [
            1 => 'حاضر',
            2 => 'غائب مع عذر',
            3 => 'غائب'
        ];

        // Get current locale
        $locale = App::getLocale(); // 'ar' or 'en'

        foreach ($users as $userId) {
            $actualStatus = FacultySessionAttendanceInvite::where('faculty_session_id', $recordId)->where('user_id', $userId)->value('actual_status');

            if (in_array($userId, $sessionEmailsUser)) {
                $user = User::find($userId);
                // Users from SessionEmail should go to invitedMembers
                $positionId = User::where('id', $userId)->value('position_id');
                $attendance = FacultySessionAttendanceInvite::where('faculty_session_id', $session->id)
                    ->where('user_id', $userId)->first();
                $signature = 'غائب'; // Default message for absent users
                if ($positionId == 3) {
                    $signature = $user->signature;
                } elseif ($attendance) {
                    if ($attendance->apply_signiture == 1) {
                        // Signature applied
                        $signature = User::find($userId)->signature;
                    } elseif ($attendance->apply_signiture == 2) {
                        // Signature rejected
                        $signature = 'رفض المستخدم التوقيع';
                    }
                }

                $this->invitedMembers[] = [
                    'user_id' => $userId,
                    'signature' => $signature,
                    'name' => User::find($userId)->name,
                    'title' => $positionsName[$positionId][$locale] ?? 'بدون منصب', // Use title based on locale
                    'attendance' => $attendanceStatuses[$actualStatus] ?? 'حالة الحضور غير مغروفة',
                ];
            } elseif ($usersJob->has($userId)) {
                $user = User::find($userId);
                // Users from SessionUser with specific positions go to members
                $positionId = User::where('id', $userId)->value('position_id');
                $attendance = FacultySessionAttendanceInvite::where('faculty_session_id', $session->id)
                    ->where('user_id', $userId)->first();
                $signature = 'غائب'; // Default message for absent users
                if ($positionId == 3) {
                    $signature = $user->signature;
                } elseif ($attendance) {
                    if ($attendance->apply_signiture == 1) {
                        // Signature applied
                        $signature = User::find($userId)->signature;
                    } elseif ($attendance->apply_signiture == 2) {
                        // Signature rejected
                        $signature = 'رفض المستخدم التوقيع';
                    }
                }

                $this->members[] = [
                    'user_id' => $userId,
                    'signature' => $signature,
                    'name' => User::find($userId)->name,
                    'title' => $positionsName[$positionId][$locale] ?? 'بدون منصب', // Use title based on locale
                    'attendance' => $attendanceStatuses[$actualStatus] ?? 'حالة الحضور غير مغروفة',
                ];
            }
        }

        // Get the topics related to the session
        $agendas = FacultySessionTopic::where('faculty_session_id', $session->id)->pluck('topic_agenda_id');
        $topics = TopicAgenda::whereIn('id', $agendas)->pluck('topic_id');
        $this->topics = Topic::whereIn('id', $topics)->pluck('title', 'id');
        $topicIds = $this->topics->keys(); // Extract the topic IDs

        $decisions = FacultySessionDecision::whereIn('faculty_session_id', [$session->id])
            ->whereIn('topic_id', $topicIds)
            ->with('topic') // Ensure we load the topic relation
            ->get();

        foreach ($decisions as $decision) {
            $decisionApproval = $decision->approval;
        }

        $this->decisionApproval = $decisionApproval;
        // Format the decisions to include topic titles
        // Define the mapping array
        $decisionStatusMap = [
            1 => 'موافقة بالاجماع',
            2 => 'رفض بالاجماع',
            3 => 'موافقة بالاغلبية',
            4 => 'رفض بالاغلبية',
            5 => 'ترك القرار لرئيس القسم',
        ];

        $this->decisions = $decisions->mapWithKeys(function ($decision) use ($decisionStatusMap) {
            return [
                $decision->id => [
                    'decision' => $decision->decision,
                    'decision_status' => $decisionStatusMap[$decision->decision_status] ?? 'حالة غير معروفة', // Replace the numeric value with Arabic text
                    'order' => $decision->order,
                    'topic_title' => $decision->topic->title, // Assuming 'topic' is the relation name
                ],
            ];
        });
    }

    public function arabicOrdinal($number)
    {
        $ordinals = [
            1 => 'الأول',
            2 => 'الثاني',
            3 => 'الثالث',
            4 => 'الرابع',
            5 => 'الخامس',
            6 => 'السادس',
            7 => 'السابع',
            8 => 'الثامن',
            9 => 'التاسع',
            10 => 'العاشر',
            11 => 'الحادى عشر',
            12 => 'الثانى عشر',
            13 => 'الثالث عشر',
            14 => 'الرابع عشر',
            15 => 'الخامس عشر',
            16 => 'السادس عشر',
            17 => 'السابع عشر',
            18 => 'الثامن عشر',
            19 => 'التاسع عشر',
            20 => 'العشرين',
        ];

        return $ordinals[$number] ?? "$number";
    }
    public function getTitle(): string|Htmlable
    {
        return __('Attandence List');
    }
}
