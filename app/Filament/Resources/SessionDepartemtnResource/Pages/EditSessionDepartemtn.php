<?php

namespace App\Filament\Resources\SessionDepartemtnResource\Pages;

use App\Filament\Resources\SessionDepartemtnResource;
use App\Models\AgandesTopicForm;
use App\Models\ControlReport;
use App\Models\CoverLetterReport;
use App\Models\Department;
use App\Models\Department_Council;
use App\Models\Session;
use App\Models\SessionEmail;
use App\Models\SessionTopic;
use App\Models\SessionUser;
use App\Models\Topic;
use App\Models\TopicAgenda;
use App\Models\User;
use App\Models\YearlyCalendar;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasPermissions;

class EditSessionDepartemtn extends EditRecord
{
    protected static string $resource = SessionDepartemtnResource::class;

    public function mount($record): void
    {

        // Check if the user is authorized to edit this record
        $session = Session::find($record); // Assuming Session is your model
        $res = $session->responsible_id;
        $cre = $session->created_by;
        // dd($session->status);
        if (!$session) {
            abort(404, 'Session not found.');
        }
        if (((int) $session->status == 1 || (auth()->user()->id != $cre && auth()->user()->id != $res))) {
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $startDate = Carbon::parse($data['start_time']);

        // Cast total_hours to an integer
        $totalhours = intval($data['total_hours']);

        // Add the total hours to the start date
        $endDateCarbon = $startDate->addHours($totalhours);

        $dateString = $endDateCarbon->toDateTimeString();

        $data['scheduled_end_time'] = $dateString;

        $agendaId = SessionTopic::where('session_id', $data['id'])->pluck('topic_agenda_id');

        // Fetch user IDs associated with the session
        $users = SessionUser::where('session_id', $data['id'])->pluck('user_id');

        // Fetch names and IDs of these users
        $invites = User::whereIn('id', $users)->pluck('id')->toArray();
        $userNameInvite = User::whereIn('id', $invites)->pluck('name')->toArray();

        // Assign the name and ID pairs to the invitations key
        $data['invitations'] = $userNameInvite;  // Only IDs here, not names
        // Retrieve and return the TopicAgenda options
        $TopicAgendaId = TopicAgenda::whereIn('id', $agendaId)
            ->where('department_id', $data['department_id'])
            ->where('status', 1)
            ->where(function ($query) use ($data) {
                // First, check if there are any matching `topic_id` in `college_councils`
                $query->whereExists(function ($query) use ($data) {
                    $query->select(DB::raw(1))
                        ->from('college_councils')
                        ->whereColumn('college_councils.topic_id', 'topics_agendas.id')
                        ->whereIn('college_councils.status', [2, 3]);
                })
                    // If no matching records in `college_councils`, apply the second condition
                    ->orWhereNotIn('id', function ($query) use ($data) {
                        $query->select('topic_agenda_id')
                            ->from('session_topics')
                            ->where('session_id', '!=', $data['id']);
                    });
            })
            ->pluck('id')
            // ->pluck('name')
            ->toArray();

        $AgendaNames = self::initializeTopicsWithoutDecision($TopicAgendaId);

        // Strip HTML tags from each value while preserving the keys
        $cleanedAgendaNames = collect($AgendaNames)->mapWithKeys(function ($value, $key) {
            $nameOfAgenda = TopicAgenda::where('id', $key)->value('name');

            // Split the name of agenda into two parts
            [$beforeColon, $afterSlash] = [
                strstr($nameOfAgenda, ':', true), // Part before the colon
                trim(substr(strstr($nameOfAgenda, '/'), 1)), // Part after the slash
            ];

            return [$key => $beforeColon . ' : ' . strip_tags($value) . ' / ' . $afterSlash];
            // return [$beforeColon . ' : ' . strip_tags($value) . ' / ' . $afterSlash];
        })->values()->toArray(); // Use values() to reset the keys to 0, 1, 2...

        $data['TopicAgendaId'] = $cleanedAgendaNames;

        $sessionEmails = SessionEmail::where('session_id', $data['id'])->get();

        foreach ($sessionEmails as $sessionEmail) {
            $userEmailData[] = [
                'id' => $sessionEmail['id'],
                'name' => $sessionEmail['name'],
                'email' => $sessionEmail['email'],
            ];
        }

        $data['email_invitations'] = $userEmailData ?? [];

        return $data;
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status'] = 0;

        // Fetch the session based on the ID provided in $data
        $session = $this->record;
        // Retrieve existing data from the database
        $existSessionEmailIds = SessionEmail::where('session_id', $data['id'])->pluck('id')->toArray();
        // // Handle invitations
        // if (isset($data['invitations'])) {
        //     $mainInvitesSecAndhead = Department_Council::where('department_id', $session['department_id'])
        //         ->where(function ($query) {
        //             $query->where('position_id', 3)
        //                 ->orWhere('position_id', 2);
        //         })
        //         ->pluck('user_id')
        //         ->toArray(); // Convert collection to array

        //     $Userids = [];
        //     $Usernames = [];

        //     foreach ($data['invitations'] as $item) {
        //         if (is_numeric($item)) {
        //             $Userids[] = $item;
        //         } else {
        //             $Usernames[] = $item;
        //         }
        //     }

        //     // Query to fetch user IDs based on usernames
        //     $userIdsFromNames = User::whereIn('name', $Usernames)->pluck('id')->toArray();

        //     // Merge fetched user IDs with existing $Userids and $mainInvitesSecAndhead
        //     $mergedUserIds = array_unique(array_merge($Userids, $userIdsFromNames, $mainInvitesSecAndhead));

        //     if (!empty($mergedUserIds)) {
        //         // Assuming $session is your Session model instance
        //         $session->users()->sync($mergedUserIds);
        //     }
        // }


        // Handle topic agendas
        if (isset($data['TopicAgendaId'])) {
            // Separate numeric IDs from non-numeric names
            $agendaIds = [];
            $agendaCodes = [];

            foreach ($data['TopicAgendaId'] as $item) {
                if (is_numeric($item)) {
                    $agendaIds[] = $item;
                } else {
                    if (preg_match_all('/\d+/', $item, $matches) && count($matches[0]) >= 2) {
                        $agendaOrder = $matches[0][0]; // First number
                        $departmentCode = $matches[0][1];  // Second number
                        $agendaCode = $departmentCode . '_' . $agendaOrder;
                        $agendaCodes[] = $agendaCode;
                    }
                }
            }

            // Fetch IDs from codes
            $fetchedAgendaIds = TopicAgenda::whereIn('code', $agendaCodes)->pluck('id')->toArray();

            // Merge fetched IDs with existing IDs
            $mergedAgendaIds = array_unique(array_merge($fetchedAgendaIds, $agendaIds));
            $data['TopicAgendaId'] = $mergedAgendaIds;

            // Fetch the report contents and other details
            $topicAgendas = TopicAgenda::whereIn('id', $mergedAgendaIds)->get(['id', 'topic_id']);
            $topicIdMap = $topicAgendas->pluck('topic_id', 'id')->toArray();

            // Fetch report content based on the topic IDs
            $reportContents = ControlReport::whereIn('topic_id', array_values($topicIdMap))->pluck('content', 'topic_id')->toArray();
            $topicsFormates = ControlReport::whereIn('topic_id', array_values($topicIdMap))->pluck('topic_formate', 'topic_id')->toArray();
            $coverLetterContents = CoverLetterReport::whereIn('topic_id', array_values($topicIdMap))->pluck('content', 'topic_id')->toArray();

            // Prepare the data for the pivot table
            $syncData = [];
            foreach ($mergedAgendaIds as $agendaId) {
                // Get the topic_id based on the TopicAgenda ID
                $topicId = $topicIdMap[$agendaId] ?? null;

                // Get report and cover letter content based on topicId, or set as null if not found
                $reportContent = $reportContents[$topicId] ?? null;
                $coverletterContent = $coverLetterContents[$topicId] ?? null;
                $topicsFormate = $topicsFormates[$topicId] ?? null;

                // Retrieve the escalation authority for the current TopicAgenda
                $agendaEscalationAuthority = TopicAgenda::where('id', $agendaId)->value('escalation_authority');

                // Prepare data array for syncing with pivot table
                $syncData[$agendaId] = [
                    'topic_formate' => $topicsFormate,
                    'report_template_content' => $reportContent,
                    'cover_letter_template_content' => $coverletterContent,
                    'escalation_authority' => $agendaEscalationAuthority,
                ];
            }

            // Sync the topic agendas with the session
            $session->topicAgenda()->sync($syncData);
        }

        // Handle email invitations
        if (isset($data['email_invitations'])) {
            $emails = $data['email_invitations'];
            $userEmailIds = [];
            $existSessionEmailIds = SessionEmail::where('session_id', $data['id'])->pluck('id')->toArray();

            foreach ($emails as $email) {
                $userId = User::where('email', $email['email'])->value('id');

                // Check if user with this email exists
                if ($userId) {
                    if (isset($email['id'])) {
                        array_push($userEmailIds, $email['id']);
                        $exist_links = SessionEmail::find($email['id']);
                        if ($exist_links) {
                            $exist_links->update([
                                'name' => $email['name'],
                                'email' => $email['email'],
                                'user_id' => $userId,
                                'updated_at' => now()
                            ]);
                        }
                    } else {
                        SessionEmail::create([
                            'name' => $email['name'],
                            'email' => $email['email'],
                            'session_id' => $data['id'],
                            'user_id' => $userId,
                            'created_at' => now(),
                            'updated_at' => null
                        ]);
                    }
                } else {
                    // Skip insertion if user with this email does not exist
                    continue;
                }
            }

            // Delete entries in SessionEmail that are not in $userEmailIds
            $not_exist_data = array_diff($existSessionEmailIds, $userEmailIds);
            foreach ($not_exist_data as $sessionData) {
                SessionEmail::find($sessionData)->delete();
            }
        }

        return $data;
    }
    public static function arabicOrdinal($number)
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
            11 => 'الحادية عشرة',
            12 => 'الثانية عشرة',
            13 => 'الثالثة عشرة',
            14 => 'الرابعة عشرة',
            15 => 'الخامسة عشرة',
            16 => 'السادسة عشرة',
            17 => 'السابعة عشرة',
            18 => 'الثامنة عشرة',
            19 => 'التاسعة عشرة',
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

    public static function initializeTopicsWithoutDecision($agendaIds): array
    {
        // $topicFormate = SessionTopic::where('session_topics.session_id', $session->id)
        $topicFormate = TopicAgenda::whereIn('topics_agendas.id', $agendaIds)
            ->join('topics as sub_topic', 'sub_topic.id', '=', 'topics_agendas.topic_id')
            ->join('control_reports as report', 'report.topic_id', '=', 'sub_topic.id')
            ->join('topics as main_topic', 'main_topic.id', '=', 'sub_topic.main_topic_id')
            ->orderBy('sub_topic.main_topic_id', 'asc') // Order by the `main_topic_id`
            ->select(
                'topics_agendas.id as agenda_id',      // Agenda ID
                'sub_topic.id as topic_id',           // Sub-topic ID
                'sub_topic.title as topic_title',     // Sub-topic Title
                'main_topic.title as main_topic',     // Main-topic Title
                'report.topic_formate'        // Topic format (if exists in `topics_agendas`)
            )
            ->get();

        // Map through all topics and use agenda_id as the key
        $formattedTopics = $topicFormate->mapWithKeys(function ($topic) {
            if (!is_null($topic->topic_formate) && $topic->topic_formate != "<p><br></p>") {
                // Pass individual topic, not grouped
                $replacements = self::getTopicReplacements($topic, $topic->topic_formate);

                // Replace the placeholders with actual values
                $content = self::replacePlaceholders($topic->topic_formate, $replacements);
                $value = $content;
            } else {
                $value = $topic->topic_title;
            }

            // Use agenda_id as the key
            return [$topic->agenda_id => $value];
        })->toArray();

        return $formattedTopics;
    }
    public static function replacePlaceholders($content, $replacements)
    {
        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        return $content;
    }
    public static function getTopicReplacements($topicData, $reportTemplate)
    {
        $agenda = TopicAgenda::findOrFail($topicData->agenda_id);
        $userId = TopicAgenda::where('id', $topicData->agenda_id)->value('created_by');
        $topicTitle = Topic::where('id', $topicData->topic_id)->value('title');
        $topicIds = is_array($topicData->topic_id) ? $topicData->topic_id : [$topicData->topic_id];
        $username = User::where('id', $userId)->value('name');

        // Fetch content and ensure it is properly formatted as an array
        $topicagendacontentform = AgandesTopicForm::where('agenda_id', $topicData->agenda_id)
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
            // '{session_number}' => $session->code,
            '{department_name}' => $agenda->departement->ar_name,
            '{faculty_name}' => $agenda->departement->faculty->ar_name,
            '{name_of_topic}' => $topicTitle ?? '',
            // '{deescion_number}' => $decision->order ?? '',
            // '{vote}' => $this->getDecisionStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            // '{vote_type}' => $this->getDecisionTypeStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            // '{justification}' => $decision->decision ?? '',
            // '{decision}' => $decision->decisionChoice ?? '',
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
    private function sessionArabicOrdinal($number)
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
}
