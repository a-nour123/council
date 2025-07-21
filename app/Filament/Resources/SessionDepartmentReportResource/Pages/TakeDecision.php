<?php

namespace App\Filament\Resources\SessionDepartmentReportResource\Pages;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Filament\Resources\SessionDepartmentReportResource;
use App\Models\{
    AgandesTopicForm,
    Session,
    CollegeCouncil,
    SessionTopic,
    Topic,
    TopicAgenda,
    User
};
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class TakeDecision extends Page
{
    protected static string $resource = SessionDepartmentReportResource::class;
    protected static string $view = 'filament.resources.college-council.pages.decision';

    public $session, $topics, $facName, $depName;

    public function mount($recordId)
    {
        if (auth()->user()->position_id == 5) {

            // if sesssion already has token action
            if (!CollegeCouncil::where('session_id', $recordId)->exists()) {
                $session = Session::findOrFail($recordId);
                $sessionTopics = SessionTopic::where('session_topics.session_id', $session->id)
                    ->join('topics_agendas as agenda', 'agenda.id', '=', 'session_topics.topic_agenda_id')
                    ->join('topics as sub_topic', 'sub_topic.id', '=', 'agenda.topic_id')
                    ->join('topics as main_topic', 'main_topic.id', '=', 'sub_topic.main_topic_id')
                    ->select(
                        'session_topics.topic_formate',
                        'sub_topic.id as topic_id',
                        'sub_topic.title as topic_title',
                        DB::raw('CAST(sub_topic.order AS SIGNED) as topic_order'),
                        'main_topic.title as main_topic',
                        DB::raw('CAST(main_topic.order AS SIGNED) as main_topic_order'),
                        'agenda.id as agenda_id',
                        'agenda.escalation_authority as agenda_escalation'
                    )
                    ->orderBy('main_topic_order', 'asc')
                    ->orderBy('topic_order', 'asc')
                    ->orderBy('agenda.id', 'asc')
                    ->get();

                $groupedTopics = $sessionTopics->map(function ($topic) use ($session) {
                    if (!is_null($topic->topic_formate) && $topic->topic_formate != "<p><br></p>") {
                        // Pass individual topic, not the group
                        $replacements = $this->getTopicReplacements($topic, $session, $topic->topic_formate);

                        // Replace the placeholders with actual values
                        $content = $this->replacePlaceholders($topic->topic_formate, $replacements);
                        $topicTitle = $content;
                    } else {
                        $topicTitle = $topic->topic_title;
                    }
                    $topic->agenda_topic = $topicTitle;
                    return $topic;
                });

                $this->session = $session;
                // $this->topics = $sessionTopics;
                $this->topics = $groupedTopics;
            } else {
                abort(403, "Sorry session already has decision");
            }
        } else {
            abort(403);
        }
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

    protected function getTopicReplacements($topicData, $session, $reportTemplate)
    {
        // dd($topicData);
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
        $departmentName = $session->department->ar_name;
        $facultyName = $session->department->faculty->ar_name;

        // Initialize the replacements array
        $replacements = [
            '{session_number}' => $session->code,
            '{department_name}' => $departmentName,
            '{faculty_name}' => $facultyName,
            '{name_of_topic}' => $topicTitle ?? '',
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

    public function getTitle(): string|Htmlable
    {
        return __("Decision of session's topics");
    }
}
