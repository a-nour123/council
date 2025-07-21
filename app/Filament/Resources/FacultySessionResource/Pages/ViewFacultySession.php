<?php

namespace App\Filament\Resources\FacultySessionResource\Pages;

use App\Filament\Resources\FacultySessionResource;
use App\Models\AgandesTopicForm;
use App\Models\Department;
use App\Models\FacultySession;
use App\Models\FacultySessionAttendanceInvite;
use App\Models\FacultySessionEmail;
use App\Models\FacultySessionTopic;
use App\Models\FacultySessionUser;
use App\Models\Topic;
use App\Models\TopicAgenda;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Actions\Action as NotificationsAction;

class ViewFacultySession extends ViewRecord
{
    protected static string $resource = FacultySessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(function ($record) {
                    // return ($record->responsible_id == auth()->id() || $record->created_by == auth()->id()) && $record->status != 1;
                    return ($record->created_by == auth()->id()) && $record->status != 1;
                }),
            Action::make('Attendance')
                ->translateLabel()
                ->visible(function ($record) {
                    $todayFormatted = Carbon::now()->startOfDay(); // Set to the start of the current day
                    // Convert the start_time string to a Carbon instance
                    $startTime = Carbon::parse($record->start_time);
                    return ($record->responsible_id != auth()->id() && $record->created_by != auth()->id() && $startTime->greaterThanOrEqualTo($todayFormatted));
                })
                ->form(function ($record) {
                    // Fetch existing record if it exists
                    $existingRecord = FacultySessionAttendanceInvite::where('faculty_session_id', $record->id)
                        ->where('user_id', auth()->id())
                        ->first();

                    // Initialize form data with default values
                    $formData = [
                        'attendance_status' => null,
                        'absence_reason' => null,
                    ];

                    // Set default values if existing record exists
                    if ($existingRecord) {
                        $formData['attendance_status'] = $existingRecord->status;
                        $formData['absence_reason'] = $existingRecord->absent_reason;
                    }

                    return [
                        Select::make('attendance_status')
                            ->label(__('Attendance Status'))
                            ->options([
                                '1' => __('Attend'),
                                '2' => __('Absent with reason'),
                                // '3' => 'Absent', // Uncomment if needed
                            ])
                            ->default(fn() => $formData['attendance_status'])
                            ->required()
                            ->reactive(),

                        Textarea::make('absence_reason')
                            ->label(__('Reason for Absence'))
                            ->placeholder(__('Please provide the reason for absence'))
                            ->default(fn() => $formData['absence_reason'])
                            ->hidden(fn($get) => $get('attendance_status') !== '2')
                            ->required(fn($get) => $get('attendance_status') === '2'),
                    ];
                })
                ->action(function (array $data, $record): void {
                    // Find existing record or create a new one
                    $userRecord = FacultySessionAttendanceInvite::updateOrCreate(
                        [
                            'faculty_session_id' => $record->id,
                            'user_id' => auth()->id(),
                        ],
                        [
                            'status' => $data['attendance_status'],
                            'absent_reason' => $data['absence_reason'] ?? null,
                        ]
                    );

                    // Display success notification
                    Notification::make()
                        ->title(__('Attendance saved successfully'))
                        ->send();
                }),
            // Display user attendance status if it exists
            Action::make('User Attendance Status')
                ->label(fn($record) => $this->getUserAttendanceStatus($record))
                ->color('gray')
                ->visible(function ($record) {
                    // $userEmail = auth()->user()->email;

                    // // email invitations of this session
                    // $emailInvitions = $record->emailInvitesEmails;

                    // // invitations of this session
                    // $invitions = $record->users;
                    // $invitionsArray = $invitions->toArray();
                    // $invitionsUsersIds = array_column($invitionsArray, 'id');

                    // $todayFormatted = Carbon::now()->toDateTimeString();
                    // // dd($invitionsUsersIds, $emailInvitions, $userEmail);
                    // if (
                    //     (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin') )
                    //     && (!in_array($userEmail, $emailInvitions) && !in_array(auth()->user()->id, $invitionsUsersIds))
                    //     && ($record->start_time >= $todayFormatted || $record->start_time <= $todayFormatted)
                    // ) {
                    //     return false;
                    // }
                    // elseif (($record->responsible_id != auth()->id() && $record->created_by != auth()->id()) && ($record->start_time >= $todayFormatted || $record->start_time <= $todayFormatted)) {
                    //     return true;
                    // }
                    // else {
                    //     return false;
                    // }
                    $todayFormatted = Carbon::now()->toDateTimeString();
                    return (($record->responsible_id != auth()->id() && $record->created_by != auth()->id()) && ($record->start_time >= $todayFormatted || $record->start_time <= $todayFormatted));
                })
                ->disabled(),

            Action::make('Status')
                ->visible(fn(FacultySession $session, $record): bool => (auth()->user()->id == $session->responsible_id && $record->status != 1 && $record->status != 2))
                ->translateLabel()
                ->color('success')
                ->form(function (FacultySession $session, $record) {
                    // Fetch topics associated with each topic_agenda_id
                    $topicOptions = []; // Initialize topic options array

                    // Get topic_agenda_ids from the SessionTopic table for the current session
                    $topicAgendaIds = FacultySessionTopic::where('faculty_session_id', $record->id)->pluck('topic_agenda_id')->unique();
                    foreach ($topicAgendaIds as $agendaId) {
                        $topicAgenda = TopicAgenda::where('id', $agendaId)->first();
                        if ($topicAgenda) {
                            $topic = Topic::find($topicAgenda->topic_id);
                            $topicTitle = strip_tags(self::initializeTopicsWithoutDecision($record)[$topicAgenda->id]);
                            if ($topic) {
                                // $topicOptions[$topicAgenda->id] = $topic->title;
                                $topicOptions[$topicAgenda->id] = $topicTitle;
                            }
                        }
                    }

                    // If no topics are found, provide a default option
                    if (empty($topicOptions)) {
                        $topicOptions = [0 => __('No topics available')];
                    }
                    $options = [
                        // 0 => __('Pending'),
                        1 => __('Accepted'),
                        2 => __('Rejected'),
                        // 3 => __('Reject with reason'),
                    ];

                    $todayFormatted = Carbon::now()->startOfDay(); // Set to the start of the current day

                    // Convert the start_time string to a Carbon instance
                    $startTime = Carbon::parse($record->start_time);

                    // Check if the start time is today or in the future
                    if (!$startTime->greaterThanOrEqualTo($todayFormatted)) {
                        // Filter the options if the condition is met
                        $options = array_filter($options, function ($key) {
                            return in_array($key, [2, 3]);
                        }, ARRAY_FILTER_USE_KEY);
                    }

                    return [
                        Select::make('status')
                            ->translateLabel()
                            ->native(false)
                            ->options($options)
                            ->required()
                            ->reactive()
                            ->validationMessages([
                                'required' => __('required validation'),
                            ]),
                        Select::make('Agenda_id') // New select for topic selection
                            ->label(__("Topics"))
                            ->native(false)
                            ->options(function ($record) use ($topicOptions) {
                                // Add "Select All" as an option at the beginning of the options list
                                return ['select_all' => __('Select All')] + $topicOptions;
                            }) // Dynamically generated options for topics, with "Select All"
                            ->reactive()
                            ->multiple()
                            ->hidden(fn(Get $get) => $get('status') != 1) // Make the topic select visible only if status is 1 (Accepted)
                            ->required(fn(Get $get) => $get('status') == 1) // Make the topic field required if status is 1 (Accepted)
                            ->validationMessages([
                                'required' => __('Please select a topic'),
                            ])
                            ->afterStateUpdated(function ($set, $state) use ($topicOptions) {
                                // If "Select All" is clicked, select all topics
                                if (in_array('select_all', $state)) {
                                    // Get all the topic IDs and set them as selected
                                    $set('Agenda_id', array_keys($topicOptions));
                                }
                            }),


                        Textarea::make('reject_reason')
                            ->translateLabel()
                            // ->hidden(fn(Get $get): bool => !($get('status') == 3)) // hidden if status is reject with reason
                            ->hidden(fn(Get $get): bool => !($get('status') == 2)) // hidden if status isn't rejected
                            ->required()
                            ->placeholder(__('Enter rejection reason here'))
                            ->validationMessages([
                                'required' => __('required validation'),
                            ]),
                    ];
                })
                ->action(function (array $data, $record): void {
                    $sessionId = $record->id;
                    $sessionDepartmentName = Department::where('id', $record->department_id)->value('ar_name');
                    $sessionInvitations = FacultySessionUser::where('faculty_session_id', $sessionId)->pluck('user_id')->toArray();
                    $sessionEmailInvitations = FacultySessionEmail::where('faculty_session_id', $sessionId)->pluck('user_id')->toArray();

                    $sessionUsers = array_merge($sessionInvitations, $sessionEmailInvitations);
                    $usersReciveNotification = User::whereIn('id', $sessionUsers)
                        ->whereNotIn('id', [$record->responsible_id, $record->created_by]) // don't take the head and secretary of department
                        ->get();

                    $status = $data['status'];
                    $reject_reason = $data['reject_reason'] ?? null;

                    $sessionAgendaIds = FacultySessionTopic::where('faculty_session_id', $sessionId)->pluck('topic_agenda_id');
                    $agendaIds = $data['Agenda_id'] ?? []; // Get selected Agenda IDs from the form data

                    foreach ($sessionAgendaIds as $agendaId) {
                        $agenda = TopicAgenda::findOrFail($agendaId);
                        if ($status == 1) {
                            $updates = 3; // accepted from department_council
                        } else {
                            $updates = 4; // rejected from department_council
                        }
                        $agenda->update([
                            "updates" => $updates
                        ]);
                    }
                    // Sync selected agendas with the session if status is accepted
                    if ($status == 1) {
                        $record->topicAgenda()->sync($agendaIds);
                    }
                    $record->update([
                        'status' => (int) $status,
                        'reject_reason' => $reject_reason,
                    ]);


                    // if ($status == 1) {
                    //     $sessionAgendaIds = SessionTopic::where('session_id', $sessionId)->pluck('topic_agenda_id');
                    //     foreach ($sessionAgendaIds as $sessionAgendaId) {
                    //         $agenda = TopicAgenda::findOrFail($sessionAgendaId);
                    //         $agenda->update([
                    //             'status' => 3 // accepted from department council
                    //         ]);
                    //     }
                    // }

                    // Display success notification
                    Notification::make()
                        ->title(__('Status saved successfully'))
                        ->color('success')
                        ->success()
                        ->send();

                    // send notifications for invited users when the statuss is accepted
                    if ($status == 1) {
                        $appURL = env('APP_URL');

                        // Build the URL dynamically
                        $url = $appURL . '/admin/faculty-sessions/' . $sessionId;

                        Notification::make()
                            ->title('لقد تمت دعوتك لحضور جلسة مجلس كلية')
                            ->body('كلية' . ': ' . $sessionDepartmentName . ' جلسة رقم: ' . $record->code)
                            ->actions([
                                NotificationsAction::make('view')
                                    ->label('عرض الجلسة')
                                    ->button()
                                    ->url($url, shouldOpenInNewTab: true)
                                    ->markAsRead(),
                            ])
                            ->sendToDatabase($usersReciveNotification);
                    }
                }),
            Action::make('Session Topics')
                ->color('danger')
                ->label(__('Session Topics'))
                ->icon('heroicon-o-clipboard-document-check')
                ->action(action: function ($record) {
                    $appURL = env('APP_URL');

                    // Build the URL dynamically
                    $url = $appURL . '/admin/faculty-sessions/faculty-session-topics/' . $record->id;

                    return redirect()->away($url);
                }),
            Action::make('Print Report')
                ->color('warning')
                ->label(__('View report to print'))
                ->icon('heroicon-o-printer')
                ->hidden(fn($record) => !$record->actual_end_time)
                ->url(fn($record) => env('APP_URL') . '/admin/faculty-sessions/details-report/' . $record->id . '/report/pdf')
                ->openUrlInNewTab(),

            // Action::make('Print Topics')
            //     ->color('info')
            //     ->label(__('View topics to print'))
            //     ->icon('heroicon-o-printer')
            //     ->hidden(fn($record) => !$record->actual_end_time)
            //     ->action(action: function ($record) {
            //         $appURL = env('APP_URL');

            //         // Build the URL dynamically
            //         $url = $appURL . '/admin/session-departments/details-report/' . $record->id . '/topics/pdf';

            //         return redirect()->away($url);
            //     }),
        ];
    }



    private function getUserAttendanceStatus($record): string
    {

        $userRecord = FacultySessionAttendanceInvite::where('faculty_session_id', $record->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($userRecord) {
            switch ($userRecord->status) {
                case '1':
                    return __('You will attend');
                case '2':
                    return __('You will be absent with reason:') . '' . $userRecord->absent_reason;
                case '3':
                    return __('You will be absent');
                default:
                    return __('You dont decided Attandence');
            }
        }

        return __('No attendance record');
    }

    private function initializeTopicsWithoutDecision(FacultySession $session)
    {
        $topicFormate = FacultySessionTopic::where('faculty_session_topics.faculty_session_id', $session->id)
            ->join('topics_agendas as agenda', 'agenda.id', '=', 'faculty_session_topics.topic_agenda_id')
            ->join('topics as sub_topic', 'sub_topic.id', '=', 'agenda.topic_id')
            ->join('topics as main_topic', 'main_topic.id', '=', 'sub_topic.main_topic_id')
            ->orderBy('sub_topic.main_topic_id', 'asc') // Order by the main_topic_id column from topics table
            ->select(
                'faculty_session_topics.topic_formate',
                'sub_topic.id as topic_id',
                'sub_topic.title as topic_title',
                'main_topic.title as main_topic',
                'agenda.id as agenda_id'
            )
            ->get();

        // Map through all topics and use agenda_id as the key
        $formattedTopics = $topicFormate->mapWithKeys(function ($topic) use ($session) {
            if (!is_null($topic->topic_formate) && $topic->topic_formate != "<p><br></p>") {
                // Pass individual topic, not grouped
                $replacements = $this->getTopicReplacements($topic, $session, $topic->topic_formate);

                // Replace the placeholders with actual values
                $content = $this->replacePlaceholders($topic->topic_formate, $replacements);
                $value = $content;
            } else {
                $value = $topic->topic_title;
            }

            // Use agenda_id as the key
            return [$topic->agenda_id => $value];
        })->toArray();

        return $formattedTopics;

    }

    private function replacePlaceholders($content, $replacements)
    {
        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        return $content;
    }

    private function getTopicReplacements($topicData, $session, $reportTemplate)
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

        $department = $session->faculty->departments->where('faculty_id', $session->faculty->id)->first();

        // Initialize the replacements array
        $replacements = [
            '{session_number}' => $session->code,
            '{department_name}' => $department ? $department->ar_name : '',
            '{faculty_name}' => $session->faculty->ar_name,
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

    private function arabicOrdinal($number)
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
