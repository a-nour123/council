<?php

namespace App\Filament\Resources\FacultySessionResource\Pages;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Filament\Resources\FacultySessionResource;
use App\Models\{
    AgandesTopicForm,
    Session,
    SessionTopic,
    TopicAgenda,
    Department,
    Department_Council,
    Faculty,
    FacultySession,
    FacultySessionAttendanceInvite,
    FacultySessionDecision,
    FacultySessionEmail,
    FacultySessionTopic,
    FacultySessionUser,
    Position,
    SessionAttendanceInvite,
    SessionDecision,
    SessionEmail,
    SessionUser,
    Topic,
    User,
    YearlyCalendar
};
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;

class FacultySessionReport extends Page
{
    protected static string $resource = FacultySessionResource::class;
    protected static string $view = 'filament.resources.faculty-session-resource.pages.ma7derDetails';

    public $recordId, $SessionCode, $depName, $depNameEn, $facName, $facNameEn, $facDeanName, $SessionPlace;
    public $startTime, $startDate, $higriDate, $dayName, $yearName;
    public $members = [], $invitedMembers = [], $topics = [], $decisions = [];
    public $decisionApproval, $processedReports, $decisionsStatusDependOnHead = [];
    public $sessionResposibleId, $facultyMessage;
    public $signitureApplyForAuthUser, $sessionOrder;
    public $absentOrNot,$decisionApprovalReason;


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
            [$session->created_by, $session->responsible_id],
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
        $facDeanName = User::findOrFail($session->responsible_id);
        $startDateTime = Carbon::parse($session->start_time);
        $this->signitureApplyForAuthUser = FacultySessionAttendanceInvite::where('user_id', auth()->user()->id)
            ->where('faculty_session_id', $session->id)
            ->first()->apply_signiture;

        // Split the code into parts using "_"
        $parts = explode('_', $session->code);

        // Assign the parts to variables
        $yearCode = $parts[0]; // Before the first "_"
        $facultyCode = $parts[1]; // Between the first and second "_"
        // $lastPart = $parts[2]; // After the second "_"
        $sessionOrder = 'الجلسة ' . self::sessionArabicOrdinal($session->order);
        $yearName = YearlyCalendar::where('code', $yearCode)->value('name');
        $facultyArName = Faculty::where('code', $facultyCode)->value('ar_name');

        // $newSessionCode = "{$yearName}_{$facultyArName}_{$lastPart}";
        $newSessionCode = "{$yearCode}_{$facultyCode}_{$sessionOrder}";

        $this->sessionOrder = $sessionOrder;
        $this->facultyMessage = $faculty->message;
        $this->yearName = $yearName;
        $this->sessionResposibleId = $session->responsible_id;
        $this->SessionCode = $newSessionCode;
        $this->depName = $department->ar_name;
        $this->depNameEn = $department->en_name;
        $this->facName = $faculty->ar_name;
        $this->facNameEn = $faculty->en_name;
        $this->facDeanName = $facDeanName->name;
        $this->SessionPlace = $session->place;
        $this->startTime = $startDateTime->format('g:i A'); // 12-hour format with minutes and AM/PM
        $this->startTime = str_replace(['AM', 'PM'], ['ص', 'م'], $this->startTime);
        $gregorianDate = $startDateTime->format('Y-m-d'); // Uniform ISO format
        $this->startDate = $startDateTime->format('Y/m/d'); // Display format
        $this->higriDate = Hijri::DateIndicDigits('Y/m/d', $gregorianDate); // Hijri date
        $this->dayName = Hijri::DateIndicDigits('l', $startDateTime->format('l'));
        $this->absentOrNot = FacultySessionAttendanceInvite::where('faculty_session_id', $session->id)->where('user_id', auth()->user()->id)->first()->actual_status ?? null;
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
            $attendance = FacultySessionAttendanceInvite::where('faculty_session_id', $session->id)
                ->where('user_id', $userId)->value('actual_status');


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

            $memberData = [
                'user_id' => $userId,
                'name' => $user->name,
                'title' => $title,
                'position_id' => $positionId,
                'attendance' => $attendanceStatuses[$attendance] ?? 'حالة الحضور غير معروفة',
            ];

            if (FacultySessionEmail::where('faculty_session_id', $session->id)->pluck('user_id')->contains($userId)) {
                $this->invitedMembers[] = $memberData;
            } else {
                $this->members[] = $memberData;
            }
        }

        // Sort the members array based on position_id priority
        usort($this->members, function ($a, $b) {
            $priority = [5, 4, 3, 2, 1];
            $posA = array_search($a['position_id'], $priority);
            $posB = array_search($b['position_id'], $priority);
            return $posA - $posB;
        });

        // Sort the invitedMembers array based on position_id priority
        usort($this->invitedMembers, function ($a, $b) {
            $priority = [5, 4, 3, 2, 1];
            $posA = array_search($a['position_id'], $priority);
            $posB = array_search($b['position_id'], $priority);
            return $posA - $posB;
        });
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
        // Retrieve topic_agenda_ids ordered by ascending order
        $agendas = FacultySessionTopic::where('faculty_session_topics.faculty_session_id', $session->id)
            ->join('topics_agendas as agendas', 'agendas.id', '=', 'faculty_session_topics.topic_agenda_id')
            ->join('topics as sub_topic', 'sub_topic.id', '=', 'agendas.topic_id') // Corrected the join condition
            ->join('topics as main_topic', 'main_topic.id', '=', 'sub_topic.main_topic_id')
            ->orderByRaw('CAST(main_topic.order AS SIGNED) ASC') // Order by main_topic.order as integer
            ->orderByRaw('CAST(sub_topic.order AS SIGNED) ASC') // Order by sub_topic.order as integer
            ->orderBy('faculty_session_topics.topic_agenda_id', 'asc') // Then order by the topic_agenda_id
            ->pluck('faculty_session_topics.topic_agenda_id'); // Pluck only the topic_agenda_id

        // Retrieve topic_ids associated with the ordered topic_agenda_ids
        $topics = TopicAgenda::whereIn('topics_agendas.id', $agendas)
            ->join('topics', 'topics.id', '=', 'topics_agendas.topic_id')
            ->orderBy('topics.main_topic_id', 'asc')
            ->orderBy('topics.order', 'asc') // Order by the topic's order first
            ->orderBy('topic_id', 'asc')  // Ascending order of topic_agenda_id
            ->pluck('topics_agendas.topic_id');

        // Retrieve topics ordered by their ids
        $this->topics = Topic::whereIn('id', $topics)
            ->orderBy('main_topic_id', 'asc')
            ->orderBy('order', 'asc')  // Ascending order of topic ids
            ->pluck('title', 'id');
    }

    protected function initializeDecisions(FacultySession $session)
    {
        $topicIds = $this->topics->keys();

        $decisions = FacultySessionDecision::where('faculty_session_decisions.faculty_session_id', $session->id)
            ->whereIn('faculty_session_decisions.topic_id', $topicIds)
            ->join('topics', 'faculty_session_decisions.topic_id', '=', 'topics.id') // Join with topics table
            ->join('topics as main_topic', 'main_topic.id', '=', 'topics.main_topic_id')
            ->orderByRaw('CAST(main_topic.order AS SIGNED) ASC') // Order by main_topic.order as integer
            ->orderByRaw('CAST(topics.order AS SIGNED) ASC') // Order by sub_topic.order as integer
            ->orderBy('faculty_session_decisions.agenda_id', 'asc')  // Ascending order of agenda_id
            // ->with('topic') // Eager load the topic relationship
            ->select('faculty_session_decisions.*', 'topics.main_topic_id', 'topics.title') // Select fields from both tables
            ->get();


        $decisionStatusMap = $this->getDecisionStatusMap();

        $x = 0;
        $this->decisions = $decisions->reduce(function ($carry, $decision) use ($decisionStatusMap, $session, &$x) {
            $x++;
            // Step 1: Fetch the report template for the current decision
            $reportTemplate = $this->getReportTemplate($decision);

            // Step 2: If no report template exists for this decision, skip this iteration and return the carry as is
            if (!$reportTemplate) {
                return $carry;
            }

            // Step 3: Retrieve the main topic title using the main_topic_id of the current decision
            $mainTopicTitle = Topic::where('id', $decision->main_topic_id)
                ->orderBy('order', 'asc') // Smallest order first
                ->value('title');

            // Step 4: Get replacement values for the placeholders in the report template
            $replacements = $this->getDecisionReplacements($decision, $session, $reportTemplate, $x);

            // Step 5: Replace the placeholders in the template with the actual values
            $content = $this->replacePlaceholders($reportTemplate, $replacements);

            // Step 6: Retrieve the title of the topic for the decision
            $topicTitle = $this->gettitleName($decision);

            // Step 7: Set the approval status of the decision (this might be used later)
            $this->decisionApproval = $decision->approval;
            $this->decisionApprovalReason = $decision->rejected_reason ?? "";

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
        // dd($decision);
        $session = FacultySession::findOrFail($decision->faculty_session_id);
        $topicFormate = FacultySessionTopic::where('faculty_session_id', $decision->faculty_session_id)
            ->where('topic_agenda_id', $decision->agenda_id)
            ->value('topic_formate');

        if (!is_null($topicFormate) && $topicFormate != "<p><br></p>") {
            $replacements = $this->getDecisionReplacements($decision, $session, $topicFormate);

            $content = $this->replacePlaceholders($topicFormate, $replacements);

            $topicTitle = $content;
        } else {
            $topicTitle = $decision->topic->title;
        }

        return $topicTitle;
    }

    protected function getReportTemplate($decision)
    {
        // Fetch the report template content
        $tempId = FacultySessionTopic::where('faculty_session_id', $decision->faculty_session_id)
            ->where('topic_agenda_id', $decision->agenda_id)
            ->value('report_template_content');

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


    protected function getDecisionReplacements($decision, $session, $reportTemplate, $topicCount = null)
    {
        // Split the code into parts using "_"
        $parts = explode('_', $session->code);

        // Assign the parts to variables
        $yearCode = $parts[0]; // Before the first "_"
        $facultyCode = $parts[1]; // Between the first and second "_"
        // $lastPart = $parts[2]; // After the second "_"
        $sessionOrder = 'الجلسة ' . self::sessionArabicOrdinal($session->order);
        $yearName = YearlyCalendar::where('code', $yearCode)->value('name');
        $facultyArName = Faculty::where('code', $facultyCode)->value('ar_name');

        // $newSessionCode = "{$yearName}_{$facultyArName}_{$lastPart}";
        $newSessionCode = "{$yearCode}_{$facultyCode}_{$sessionOrder}";

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
        // $newDecisionNumber = $session->order . '/' . $topicCount ?? "";
        $newDecisionNumber = $session->order . '/' . $decision->agenda_order ?? "";

        // dd($newDecisionNumber);

        $sessionDepartment = SessionDecision::where('agenda_id', $decision->agenda_id)
            ->select(['session_id', 'agenda_id', 'decisionChoice', 'decision']) // Include necessary columns
            ->with([
                'session' => function ($query) {
                    $query->select('id', 'order', 'actual_start_time');
                }
            ])
            ->first();

        if ($sessionDepartment && $sessionDepartment->session) {
            $sessionDepartmentActualStartDateTime = Carbon::parse($sessionDepartment->session->actual_start_time);
            $sessionDepartmentOrder = self::sessionArabicOrdinal($sessionDepartment->session->order);
            $newDepartmentDecisionNumber = $sessionDepartment->session->order . '/' . $topicCount ?? "";
        } else {
            // Handle the case when $sessionDepartment or its session is null
            $sessionDepartmentActualStartDateTime = null;
            $sessionDepartmentOrder = null;
            $newDepartmentDecisionNumber = null;
        }

        // Extract all placeholders within curly braces
        preg_match_all('/\{(.*?)\}/', $reportTemplate, $matches);

        $placeholders = $matches[1];
        $actualStartDateTime = Carbon::parse($session->actual_start_time);

        // Initialize the replacements array
        $replacements = [
            // '{session_number}' => $session->code,
            '{session_number}' => $session->order,
            '{session_number_as_word}' => $sessionOrder,
            '{department_name}' => $this->depName,
            '{faculty_name}' => $this->facName,
            '{name_of_topic}' => $topicTitle ?? '',
            // '{number_of_topic}' => $topicCount,
            '{number_of_topic}' => $decision->agenda_order,
            '{acadimic_year}' => $session->year->name ?? '',
            // '{deescion_number}' => $decision->order ?? '',
            '{deescion_number}' => $newDecisionNumber ?? '',
            '{vote}' => $this->getDecisionStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            '{vote_type}' => $this->getDecisionTypeStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            '{justification}' => $decision->decision ?? '',
            '{decision}' => $decision->decisionChoice ?? '',
            '{uploader}' => $username,
            '{faculty_session_hijri_date}' => Hijri::DateIndicDigits('d-m-Y', $actualStartDateTime->format('d-m-Y')) ?? "",
            '{faculty_session_date}' => $actualStartDateTime->format('d-m-Y') ?? "",

            '{department_session_hijri_date}' => $sessionDepartmentActualStartDateTime
                ? Hijri::DateIndicDigits('d-m-Y', $sessionDepartmentActualStartDateTime->format('d-m-Y'))
                : "{لم يناقش هذا الموضوع فى مجلس قسم}",

            '{department_session_date}' => $sessionDepartmentActualStartDateTime
                ? $sessionDepartmentActualStartDateTime->format('d-m-Y')
                : "{لم يناقش هذا الموضوع فى مجلس قسم}",

            '{session_department_decision}' => $sessionDepartment->decisionChoice ?? "{لم يناقش هذا الموضوع فى مجلس قسم}",
            '{session_department_justification}' => $sessionDepartment->decision ?? "{لم يناقش هذا الموضوع فى مجلس قسم}",
            '{session_order_as_number}' => $sessionDepartment->session->order ?? "{لم يناقش هذا الموضوع فى مجلس قسم}",
            '{session_order}' => $sessionDepartmentOrder ?? "{لم يناقش هذا الموضوع فى مجلس قسم}",
            '{session_department_decision_number}' => $newDepartmentDecisionNumber ?? "{لم يناقش هذا الموضوع فى مجلس قسم}",
        ];

        // Check if $decodedContents is an array before looping
        if (is_array($decodedContents)) {

            // Search in the decoded content for each placeholder and add it to the replacements
            foreach ($placeholders as $placeholder) {
                foreach ($decodedContents as $formField) {
                    // dd($formField);
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
        return __('Session Report');
    }
}
