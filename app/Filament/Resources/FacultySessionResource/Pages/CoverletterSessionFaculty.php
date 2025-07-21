<?php

namespace App\Filament\Resources\FacultySessionResource\Pages;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Filament\Resources\FacultySessionResource;
use App\Models\AgandesTopicForm;
use App\Models\FacultySession;
use App\Models\FacultySessionTopic;
use App\Models\TopicAgenda;
use App\Models\Department;
use App\Models\Department_Council;
use App\Models\Faculty;
use App\Models\Position;
use App\Models\FacultySessionAttendanceInvite;
use App\Models\FacultySessionDecision;
use App\Models\FacultySessionEmail;
use App\Models\FacultySessionUser;
use App\Models\Topic;
use App\Models\User;
use App\Models\YearlyCalendar;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class CoverletterSessionFaculty extends Page
{
    protected static string $resource = FacultySessionResource::class;
    protected static string $view = 'filament.resources.faculty-session-resource.pages.CoverLetter';

    public $recordId, $SessionCode, $depName, $depNameEn, $facName, $facNameEn, $DepHeadName, $SessionPlace;
    public $startTime, $startDate, $higriDate, $dayName, $acadimicYear;
    public $members = [], $invitedMembers = [], $topics = [], $decisions = [], $fullTopics = [];
    public $decisionApproval, $processedReports, $decisionsStatusDependOnHead = [];
    public $sessionResposibleId;

    public function mount($recordId)
    {
        $session = $this->authorizeUser($recordId);

        $this->initializeSessionDetails($session);
        $this->initializeMembers($session);
        $this->initializeTopics($session);
        $this->initializeDecisions($session);
    }

    protected function authorizeUser($recordId)
    {
        $session = FacultySession::findOrFail($recordId);
        $authorizedUserIds = array_merge(
            [auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')],
            [$session->created_by, $session->responsible_id, auth()->user()->position_id == 5],
            FacultySessionUser::where('faculty_session_id', $recordId)->pluck('user_id')->toArray(),
            FacultySessionEmail::where('faculty_session_id', $recordId)->pluck('user_id')->toArray()
        );

        if (!in_array(auth()->user()->id, $authorizedUserIds)) {
            abort(403, 'You do not have access to this page.');
        }

        return $session;
    }

    protected function initializeSessionDetails(FacultySession $session)
    {
        $this->recordId = $session->id;
        $sessionTopic = FacultySessionTopic::where('faculty_session_id', $session->id)->firstOrFail();
        $topicAgenda = TopicAgenda::findOrFail($sessionTopic->topic_agenda_id);
        $department = Department::findOrFail($topicAgenda->department_id);
        $faculty = Faculty::findOrFail($topicAgenda->faculty_id);
        $depHeadName = User::findOrFail($session->responsible_id);
        $startDateTime = Carbon::parse($session->start_time);

        // Split the code into parts using "_"
        $parts = explode('_', $code);

        // Assign the parts to variables
        $yearCode = $parts[0]; // Before the first "_"
        $facultyCode = $parts[1]; // Between the first and second "_"
        // $lastPart = $parts[2]; // After the second "_"
        $sessionOrder = 'الجلسة ' . self::arabicOrdinal($session->order);
        $yearName = YearlyCalendar::where('code', $yearCode)->value('name');
        $facultyArName = Faculty::where('code', $facultyCode)->value('ar_name');

        // $newSessionCode = "{$yearName}_{$facultyArName}_{$lastPart}";
        $newSessionCode = "{$yearCode}_{$facultyCode}_{$sessionOrder}";

        $this->sessionResposibleId = $session->responsible_id;
        $this->SessionCode = $newSessionCode;
        $this->depName = $department->ar_name;
        $this->depNameEn = $department->en_name;
        $this->facName = $faculty->ar_name;
        $this->facNameEn = $faculty->en_name;
        $this->DepHeadName = $depHeadName->name;
        $this->SessionPlace = $session->place;
        $this->startTime = $startDateTime->format('g');
        $this->startDate = $startDateTime->format('Y:m:d');
        $this->higriDate = Hijri::DateIndicDigits('Y-m-d', $startDateTime->format('Y-m-d'));
        $this->dayName = Hijri::DateIndicDigits('l', $startDateTime->format('l'));
        $this->acadimicYear = $yearName;
    }

    protected function initializeMembers(FacultySession $session)
    {
        $users = $this->getSessionUsers($session);
        $positionsName = $this->getPositionsName();
        $attendanceStatuses = $this->getAttendanceStatuses();
        $locale = App::getLocale();
        foreach ($users as $userId) {
            $user = User::find($userId);
            $positionId = $user->position_id;

            // Retrieve the 'actual_status' directly for attendance
            $actualStatus = FacultySessionAttendanceInvite::where('faculty_session_id', $session->id)
                ->where('user_id', $userId)
                ->value('actual_status');

            // Determine the signature based on attendance status
            $signature = ($actualStatus == 1) ? $user->signature : 'غائب';

            $user = User::findOrFail($userId);

            // Ensure $positionsName and $locale are defined
            $title = ""; // Replace with your actual data

            if (in_array($user->position_id, [3])) {
                if ($locale == "en") {
                    $userDepartement = $user->department->en_name;
                } else {
                    $userDepartement = $user->department->ar_name;
                }
                // Ensure the department relationship exists
                $title = ($positionsName[$user->position_id][$locale]) . ' - ' . $userDepartement;
            } else {
                $title = $positionsName[$user->position_id][$locale] ?? 'بدون منصب';
            }

            // Prepare member data array
            $memberData = [
                'user_id' => $userId,
                'signature' => $signature,
                'name' => $user->name,
                'title' =>$title ,
                'attendance' => $attendanceStatuses[$actualStatus] ?? 'حالة الحضور غير معروفة',
            ];


            if (FacultySessionEmail::where('faculty_session_id', $session->id)->pluck('user_id')->contains($userId)) {
                $this->invitedMembers[] = $memberData;
            } else {
                $this->members[] = $memberData;
            }
        }
    }

    protected function getSessionUsers(FacultySession $session)
    {
        $sessionEmailsUser = FacultySessionEmail::where('faculty_session_id', $session->id)->pluck('user_id')->toArray();
        $sessionUserIds = FacultySessionUser::where('faculty_session_id', $session->id)->pluck('user_id')->toArray();

        return array_merge($sessionUserIds, $sessionEmailsUser);
    }

    protected function getPositionsName()
    {
        return [
            1 => ['en' => 'Academic Staff', 'ar' => 'عضو هيئة تدريس'],
            2 => ['en' => 'Academic Staff', 'ar' => 'عضو هيئة تدريس'],
            3 => ['en' => 'Head of Department', 'ar' => 'رئيس القسم'],
            4 => ['en' => 'Secretary of the College Council', 'ar' => 'أمين مجلس الكلية'],
            5 => ['en' => 'Dean of the College', 'ar' => 'عميد الكلية'],
            6 => ['en' => 'Vice Rector for Educational Affairs', 'ar' => 'نائب رئيس الجامعة للشؤون التعليمية'],
            7 => ['en' => 'Prex', 'ar' => 'رئيس'],
        ];
    }

    protected function getAttendanceStatuses()
    {
        return [
            1 => 'حاضر',
            2 => 'غائب مع عذر',
            3 => 'غائب'
        ];
    }

    protected function initializeTopics(FacultySession $session)
    {
        $agendas = FacultySessionTopic::where('faculty_session_id', $session->id)->pluck('topic_agenda_id');
        $topics = TopicAgenda::whereIn('id', $agendas)->pluck('topic_id');
        $this->topics = Topic::whereIn('id', $topics)->pluck('title', 'id');
    }
    // protected function initializeTopics(FacultySession $session)
    // {
    //     $agendas = SessionTopic::where('session_id', $session->id)->pluck('topic_agenda_id');
    //     $supTopicsIds = TopicAgenda::whereIn('id', $agendas)->pluck('topic_id');

    //     // Remove the `toArray()` method to keep it as a collection
    //     $supTopics = Topic::whereIn('id', $supTopicsIds)->get();

    //     // Ensure $supTopics is a collection and use `mapWithKeys` method
    //     $this->fullTopics = $supTopics->mapWithKeys(function ($supTopic) {
    //         $supTopicTitle = $supTopic->title;

    //         $mainTopicId = Topic::where('id', $supTopic->id)->value('main_topic_id');
    //         $mainTopicOrder = Topic::where('id', $mainTopicId)->value('order');
    //         $mainTopicTitle = Topic::where('id', $mainTopicId)->value('title');

    //         // Correct the string concatenation with '.'
    //         return [
    //             $mainTopicTitle . ' / ' . $mainTopicOrder => [
    //                 'sup_topic_id' => $supTopic->id,
    //                 'sup_topic_title' => $supTopicTitle,
    //             ],
    //         ];
    //     });

    //     $this->topics = Topic::whereIn('id', $supTopicsIds)->pluck('title', 'id');

    //     // dd($this->fullTopics, $this->topics);
    // }

    protected function initializeDecisions(FacultySession $session)
    {
        $topicIds = $this->topics->keys();

        $decisions = FacultySessionDecision::where('faculty_session_id', $session->id)
            ->whereIn('topic_id', $topicIds)
            ->join('topics', 'faculty_session_decisions.topic_id', '=', 'topics.id') // Join with topics table
            ->orderBy('topics.main_topic_id', 'asc') // Order by the main_topic_id column from topics table
            // ->with('topic') // Eager load the topic relationship
            ->select('faculty_session_decisions.*', 'topics.main_topic_id', 'topics.title') // Select fields from both tables
            ->get();

        $decisionStatusMap = $this->getDecisionStatusMap();

        $this->decisions = $decisions->reduce(function ($carry, $decision) use ($decisionStatusMap, $session) {
            // Step 1: Fetch the report template for the current decision
            $reportTemplate = $this->getReportTemplate($decision);

            // Step 2: If no report template exists for this decision, skip this iteration and return the carry as is
            if (!$reportTemplate) {
                return $carry;
            }

            // Step 3: Retrieve the main topic title using the main_topic_id of the current decision
            $mainTopicTitle = Topic::where('id', $decision->main_topic_id)->value('title');

            // Step 4: Get replacement values for the placeholders in the report template
            $replacements = $this->getDecisionReplacements($decision, $session, $reportTemplate);

            // Step 5: Replace the placeholders in the template with the actual values
            $content = $this->replacePlaceholders($reportTemplate, $replacements);

            // Step 6: Retrieve the title of the topic for the decision
            $topicTitle = $this->gettitleName($decision);

            // Step 7: Set the approval status of the decision (this might be used later)
            $this->decisionApproval = $decision->approval;
            // Step 8: Check if the main topic title already exists in the $carry array.
            // If it doesn't, initialize it with an empty 'details' array.
            if (!isset($carry[$mainTopicTitle])) {
                $carry[$mainTopicTitle] = [
                    'details' => [],  // Initialize the 'details' array to store subtopics
                ];
            }

            // Step 9: Append the current decision's topic details to the 'details' array of the corresponding main topic.
            $carry[$mainTopicTitle]['details'][] = [
                'cover_letter_template_content' => $content,  // The content of the report (after placeholder replacement)
                'topic_title' => $topicTitle,   // The title of the topic for this decision
            ];

            // Step 10: Return the updated $carry array to be used in the next iteration
            return $carry;
        }, []); // The second argument is an empty array, which acts as the initial value of $carry

    }

    protected function getDecisionStatusMap()
    {
        return [
            1 => 'موافقة',
            2 => 'رفض',
            3 => 'موافقة',
            4 => 'رفض',
            5 => 'تساوى',
        ];
    }
    protected function getDecisionTypeStatusMap()
    {
        return [
            1 => 'بالاجماع',
            2 => 'بالاجماع',
            3 => 'بالاغلبية',
            4 => 'بالاغلبية',
            5 => 'ترك القرار لرئيس القسم',
        ];
    }
    protected function gettitleName($decision)
    {
        $topicTitle = $decision->topic->title;
        return $topicTitle;
    }

    protected function getReportTemplate($decision)
    {
        // Fetch the report template content
        $tempId = FacultySessionTopic::where('faculty_session_id', $decision->session_id)
            ->where('topic_agenda_id', $decision->agenda_id)
            ->value('cover_letter_template_content');

        // If the template exists, clean it by decoding HTML entities and removing unnecessary spaces
        if ($tempId) {
            // Decode HTML entities
            $tempId = html_entity_decode($tempId, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Remove any extra spaces or non-breaking spaces
            $tempId = preg_replace('/\s+/', ' ', $tempId);
            $tempId = str_replace("\xC2\xA0", ' ', $tempId); // Handle non-breaking spaces directly

            // Trim the result to remove any leading or trailing spaces
            $tempId = trim($tempId);
        }
        return $tempId;
    }


    protected function getDecisionReplacements($decision, $session, $reportTemplate)
    {
        $userId = TopicAgenda::where('id', $decision->agenda_id)->value('created_by');
        $topicTitle = Topic::where('id', $decision->topic_id)->value('title');
        $topicIds = is_array($decision->topic_id) ? $decision->topic_id : [$decision->topic_id];
        $username = User::where('id', $userId)->value('name');

        // Fetch content and ensure it is properly formatted as an array
        $topicagendacontentform = AgandesTopicForm::where('agenda_id', $decision->agenda_id)
            ->whereIn('topic_id', $topicIds)
            ->pluck('content')
            ->toArray();

        // Combine all content into a single array of decoded JSON objects
        $decodedContents = [];
        foreach ($topicagendacontentform as $jsonString) {
            // Check if the element is a string and contains JSON
            if (is_string($jsonString)) {
                $decoded = json_decode($jsonString, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $decodedContents = array_merge($decodedContents, $decoded);
                } else {
                    // Log or handle invalid JSON
                    return ['error' => 'Invalid JSON content found.'];
                }
            } elseif (is_array($jsonString)) {
                // If it's already an array, just merge it
                $decodedContents = array_merge($decodedContents, $jsonString);
            } else {
                // Handle the case where $jsonString is neither a string nor an array
                return ['error' => 'Unexpected data type encountered.'];
            }
        }


        // Extract all placeholders within curly braces
        preg_match_all('/\{(.*?)\}/', $reportTemplate, $matches);

        $placeholders = $matches[1];


        // Initialize the replacements array
        $replacements = [
            '{session_number}' => $session->code,
            '{department_name}' => $this->depName,
            '{faculty_name}' => $this->facName,
            '{name_of_topic}' => $topicTitle ?? '',
            '{deescion_number}' => $decision->order ?? '',
            '{vote}' => $this->getDecisionStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            '{vote_type}' => $this->getDecisionTypeStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            '{justification}' => $decision->decision ?? '',
            '{uploader}' => $username,
        ];

        // Check if $decodedContents is an array before looping
        if (is_array($decodedContents)) {

            // Search in the decoded content for each placeholder and add it to the replacements
            foreach ($placeholders as $placeholder) {
                foreach ($placeholders as $placeholder) {
                    foreach ($decodedContents as $formField) {

                        $selectableTypes = ['select', 'checkbox-group', 'radio-group'];

                        if (in_array($formField['type'], $selectableTypes)) {
                            $values = $formField['values'];
                            $selectedLabels = [];

                            foreach ($values as $ty) {
                                if (isset($ty['selected']) && $ty['selected'] === true) {
                                    // Collect selected labels
                                    $selectedLabels[] = $ty['label'] ?? '';
                                }
                            }

                            // Implode selected labels into a single string, separated by commas
                            $formField['value'] = implode(', ', $selectedLabels);

                            // Make sure 'label' is set, if not, use the existing label
                            $formField['label'] = $formField['label'] ?? '';

                            if (isset($formField['label']) && $formField['label'] === $placeholder) {
                                // Set the replacement value with the imploded selected values
                                $replacements['{' . $placeholder . '}'] = $formField['value'] ?? '';
                            }
                        } else {
                            if (isset($formField['label']) && $formField['label'] === $placeholder) {
                                $replacements['{' . $placeholder . '}'] = $formField['value'] ?? '';
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            $replacements['error'] = 'Decoded content is not an array.';
        }

        return $replacements;
    }


    protected function replacePlaceholders($content, $replacements)
    {
        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        return $content;
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
            11 => 'الحادي عشر',
            12 => 'الثاني عشر',
            13 => 'الثالث عشر',
            14 => 'الرابع عشر',
            15 => 'الخامس عشر',
            16 => 'السادس عشر',
            17 => 'السابع عشر',
            18 => 'الثامن عشر',
            19 => 'التاسع عشر',
            20 => 'العشرون',
            21 => 'الحادي والعشرون',
            22 => 'الثاني والعشرون',
            23 => 'الثالث والعشرون',
            24 => 'الرابع والعشرون',
            25 => 'الخامس والعشرون',
            26 => 'السادس والعشرون',
            27 => 'السابع والعشرون',
            28 => 'الثامن والعشرون',
            29 => 'التاسع والعشرون',
            30 => 'الثلاثون',
        ];

        return $ordinals[$number] ?? $number;
    }

    public function getTitle(): string|Htmlable
    {
        return __('');
    }
}
