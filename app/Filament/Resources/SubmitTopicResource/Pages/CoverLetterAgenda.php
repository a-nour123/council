<?php

namespace App\Filament\Resources\SubmitTopicResource\Pages;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Filament\Resources\SubmitTopicResource;
use App\Models\AgandesTopicForm;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Session;
use App\Models\SessionAttendanceInvite;
use App\Models\SessionDecision;
use App\Models\SessionEmail;
use App\Models\SessionTopic;
use App\Models\SessionUser;
use App\Models\Topic;
use App\Models\TopicAgenda;
use App\Models\User;
use App\Models\YearlyCalendar;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;

class CoverLetterAgenda extends Page
{
    protected static string $resource = SubmitTopicResource::class;
    protected static string $view = 'filament.resources.Agandes.viewAgandaCoverLetter';
    public $recordId, $SessionCode, $facNameEn, $depNameEn, $depName, $facName, $DepHeadName, $SessionPlace;
    public $startTime, $startDate, $higriDate, $dayName, $acadimicYear;
    public $members = [], $invitedMembers = [], $topics = [], $decisions = [], $fullTopics = [];
    public $decisionApproval, $processedReports, $decisionsStatusDependOnHead = [];
    public $sessionResposibleId;

    public function mount($recordId)
    {
        #here will make the sessionId is the $recordId by this way
        $ExistSessionForThisRecord = SessionTopic::where('topic_agenda_id', $recordId)->first();
        $AgendaId = $ExistSessionForThisRecord->topic_agenda_id;
        $recordId = $ExistSessionForThisRecord->session_id;
        $session = $this->authorizeUser($recordId);
        $this->initializeSessionDetails($session, $AgendaId);
        $this->initializeMembers($session, $AgendaId);
        $this->initializeTopics($session, $AgendaId);
        $this->initializeDecisions($session);
    }

    protected function authorizeUser($recordId)
    {
        $session = Session::findOrFail($recordId);

        return $session;
    }

    protected function initializeSessionDetails(Session $session, $AgendaId)
    {
        $this->recordId = $session->id;
        $sessionTopic = SessionTopic::where('session_id', $session->id)->firstOrFail();
        $topicAgenda = TopicAgenda::findOrFail($AgendaId);
        $department = Department::findOrFail($topicAgenda->department_id);
        $faculty = Faculty::findOrFail($topicAgenda->faculty_id);
        $depHeadName = User::findOrFail($session->responsible_id);
        $startDateTime = Carbon::parse($session->start_time);
        // Split the code into parts using "_"
        $parts = explode('_', $session->code);

        // Assign the parts to variables
        $yearCode = $parts[0]; // Before the first "_"
        $departmentCode = $parts[1]; // Between the first and second "_"
        $lastPart = $parts[2]; // After the second "_"

        $yearName = YearlyCalendar::where('code', $yearCode)->value('name');
        $departmentArName = Department::where('code', $departmentCode)->value('ar_name');

        $newSessionCode = "{$yearName}_{$departmentArName}_{$lastPart}";

        $this->sessionResposibleId = $session->responsible_id;
        $this->SessionCode = $newSessionCode;
        $this->depNameEn = $department->en_name;
        $this->depName = $department->ar_name;
        $this->facName = $faculty->ar_name;
        $this->facNameEn = $faculty->en_name;
        $this->DepHeadName = $depHeadName->name;
        $this->DepHeadName = $depHeadName->name;
        $this->SessionPlace = $session->place;
        $this->startTime = $startDateTime->format('g:i'); // 12-hour format with minutes
        $gregorianDate = $startDateTime->format('Y-m-d'); // Uniform ISO format
        $this->startDate = $startDateTime->format('Y/m/d'); // Display format
        $this->higriDate = Hijri::DateIndicDigits('Y/m/d', $gregorianDate); // Hijri date
        $this->dayName = Hijri::DateIndicDigits('l', $startDateTime->format('l'));
        $this->acadimicYear = $yearName;
    }

    protected function initializeMembers(Session $session, $AgendaId)
    {
        $users = $this->getSessionUsers($session);
        $positionsName = $this->getPositionsName();
        $attendanceStatuses = $this->getAttendanceStatuses();
        $locale = App::getLocale();
        foreach ($users as $userId) {
            $user = User::find($userId);
            $positionId = $user->position_id;
            $attendance = SessionAttendanceInvite::where('session_id', $session->id)
                ->where('user_id', $userId)->first();
            $signature = 'غائب'; // Default message for absent users

            // Check if positionId is 3 and set signature directly
            if ($positionId == 3) {
                $signature = $user->signature;
            } elseif ($attendance) {
                if ($attendance->apply_signiture == 1) {
                    // Signature applied
                    $signature = $user->signature;
                } elseif ($attendance->apply_signiture == 2) {
                    // Signature rejected
                    $signature = 'رفض المستخدم التوقيع';
                }
            }
            $memberData = [
                'user_id' => $userId,
                'signature' => $signature,
                'name' => $user->name,
                'title' => $positionsName[$positionId][$locale] ?? 'بدون منصب',
                'attendance' => $attendanceStatuses[$attendance->actual_status ?? 0] ?? 'حالة الحضور غير معروفة',
            ];


            if (SessionEmail::where('session_id', $session->id)->pluck('user_id')->contains($userId)) {
                $this->invitedMembers[] = $memberData;
            } else {
                $this->members[] = $memberData;
            }
        }
    }

    protected function getSessionUsers(Session $session)
    {
        $sessionEmailsUser = SessionEmail::where('session_id', $session->id)->pluck('user_id')->toArray();
        $sessionUserIds = SessionUser::where('session_id', $session->id)->pluck('user_id')->toArray();

        return array_merge($sessionUserIds, $sessionEmailsUser);
    }

    protected function getPositionsName()
    {
        return [
            1 => ['en' => 'Academic Staff', 'ar' => 'عضو هيئة تدريس'],
            2 => ['en' => 'Secretary of the Department Council', 'ar' => 'أمين مجلس القسم'],
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

    protected function initializeTopics(Session $session, $AgendaId)
    {
        // $topics = TopicAgenda::where('id', $AgendaId)->pluck('topic_id');
        // $this->topics = Topic::whereIn('id', $topics)->pluck('title', 'id');
        $this->topics = TopicAgenda::where('id', $AgendaId)->pluck('id');
    }
    // protected function initializeTopics(Session $session)
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

    protected function initializeDecisions(Session $session)
    {
        // $topicIds = $this->topics->keys();
        $topicIds = $this->topics->values();

        $decisions = SessionDecision::where('session_id', $session->id)
            // ->whereIn('topic_id', $topicIds)
            ->whereIn('agenda_id', $topicIds)
            ->join('topics', 'session_decisions.topic_id', '=', 'topics.id') // Join with topics table
            ->orderBy('topics.main_topic_id', 'asc') // Order by the main_topic_id column from topics table
            // ->with('topic') // Eager load the topic relationship
            ->select('session_decisions.*', 'topics.main_topic_id', 'topics.title') // Select fields from both tables
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
                'report_contents' => $content,  // The content of the report (after placeholder replacement)
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
        $tempId = SessionTopic::where('session_id', $decision->session_id)
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

        $actualStartDateTime = Carbon::parse($session->actual_start_time);
        // $facultyDean = User::where('id',$decision->session->department->faculty->facultyDean->user_id)->value('name');
        $facultyDean = User::where('faculty_id', $decision->session->department->faculty->id)->value('name');
        $sessionOrder = 'الجلسة ' . self::sessionArabicOrdinal($session->order);
        $newDecisionNumber = $decision->session->order . '/' . $decision->agenda_order;

        // Initialize the replacements array
        $replacements = [
            '{session_number}' => $session->order,
            '{session_number_as_word}' => $sessionOrder,
            '{number_of_topic}' => $decision->agenda_order,
            '{acadimic_year}' => $decision->session->year->name ?? '',
            '{department_name}' => $this->depName,
            '{faculty_name}' => $this->facName,
            '{name_of_topic}' => $topicTitle ?? '',
            // '{deescion_number}' => $decision->order ?? '',
            '{deescion_number}' => $newDecisionNumber ?? '',
            '{vote}' => $this->getDecisionStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            '{vote_type}' => $this->getDecisionTypeStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            '{justification}' => $decision->decision ?? '',
            '{decision}' => $decision->decisionChoice ?? '',
            '{uploader}' => $username,
            "{department_session_hijri_date}" =>  Hijri::DateIndicDigits('d-m-Y', $actualStartDateTime->format('d-m-Y')) ?? "",
            "{department_session_date}" => $actualStartDateTime->format('d-m-Y') ?? "",
            "{session_order}" => 'الجلسة ' . $this->sessionArabicOrdinal($session->order) ?? "",
            "{session_order_as_number}" => $session->order ?? "",
            "{session_department_decision}" => $decision->decisionChoice ?? "",
            "{session_department_decision_number}" => $decision->order ?? "",
            "{session_department_justification}" => $decision->decision ?? "",
            "{faculty_dean}" => $facultyDean ?? "",
            // "{faculty_session_hijri_date}" => "",
            // "{faculty_session_date}" => "",
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
            31 => 'الحادي والثلاثون',
            32 => 'الثاني والثلاثون',
            33 => 'الثالث والثلاثون',
            34 => 'الرابع والثلاثون',
            35 => 'الخامس والثلاثون',
            36 => 'السادس والثلاثون',
            37 => 'السابع والثلاثون',
            38 => 'الثامن والثلاثون',
            39 => 'التاسع والثلاثون',
            40 => 'الأربعون',
            41 => 'الحادي والأربعون',
            42 => 'الثاني والأربعون',
            43 => 'الثالث والأربعون',
            44 => 'الرابع والأربعون',
            45 => 'الخامس والأربعون',
            46 => 'السادس والأربعون',
            47 => 'السابع والأربعون',
            48 => 'الثامن والأربعون',
            49 => 'التاسع والأربعون',
            50 => 'الخمسون',
            51 => 'الحادي والخمسون',
            52 => 'الثاني والخمسون',
            53 => 'الثالث والخمسون',
            54 => 'الرابع والخمسون',
            55 => 'الخامس والخمسون',
            56 => 'السادس والخمسون',
            57 => 'السابع والخمسون',
            58 => 'الثامن والخمسون',
            59 => 'التاسع والخمسون',
            60 => 'الستون',
        ];

        return $ordinals[$number] ?? $number;
    }

    public static function sessionArabicOrdinal($number)
    {
        $ordinals = [
            1 => 'الأولى',
            2 => 'الثانية',
            3 => 'الثالثة',
            4 => 'الرابعة',
            5 => 'الخامسة',
            6 => 'السادسة',
            7 => 'السابعة',
            8 => 'الثامنة',
            9 => 'التاسعة',
            10 => 'العاشرة',
            11 => 'الحادية عشر',
            12 => 'الثانية عشر',
            13 => 'الثالثة عشر',
            14 => 'الرابعة عشر',
            15 => 'الخامسة عشر',
            16 => 'السادسة عشر',
            17 => 'السابعة عشر',
            18 => 'الثامنة عشر',
            19 => 'التاسعة عشر',
            20 => 'العشرون',
            21 => 'الحادية والعشرون',
            22 => 'الثانية والعشرون',
            23 => 'الثالثة والعشرون',
            24 => 'الرابعة والعشرون',
            25 => 'الخامسة والعشرون',
            26 => 'السادسة والعشرون',
            27 => 'السابعة والعشرون',
            28 => 'الثامنة والعشرون',
            29 => 'التاسعة والعشرون',
            30 => 'الثلاثون',
            31 => 'الحادية والثلاثون',
            32 => 'الثانية والثلاثون',
            33 => 'الثالثة والثلاثون',
            34 => 'الرابعة والثلاثون',
            35 => 'الخامسة والثلاثون',
            36 => 'السادسة والثلاثون',
            37 => 'السابعة والثلاثون',
            38 => 'الثامنة والثلاثون',
            39 => 'التاسعة والثلاثون',
            40 => 'الأربعون',
            41 => 'الحادية والأربعون',
            42 => 'الثانية والأربعون',
            43 => 'الثالثة والأربعون',
            44 => 'الرابعة والأربعون',
            45 => 'الخامسة والأربعون',
            46 => 'السادسة والأربعون',
            47 => 'السابعة والأربعون',
            48 => 'الثامنة والأربعون',
            49 => 'التاسعة والأربعون',
            50 => 'الخمسون',
            51 => 'الحادية والخمسون',
            52 => 'الثانية والخمسون',
            53 => 'الثالثة والخمسون',
            54 => 'الرابعة والخمسون',
            55 => 'الخامسة والخمسون',
            56 => 'السادسة والخمسون',
            57 => 'السابعة والخمسون',
            58 => 'الثامنة والخمسون',
            59 => 'التاسعة والخمسون',
            60 => 'الستون',
        ];

        return $ordinals[$number] ?? $number;
    }

    public function getTitle(): string|Htmlable
    {
        return __('');
    }
}
