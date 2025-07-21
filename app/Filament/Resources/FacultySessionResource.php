<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacultySessionResource\Pages;
use App\Filament\Resources\FacultySessionResource\RelationManagers;
use App\Models\FacultySession;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SessionDepartemtnResource\Pages\SessionReport;
use App\Filament\Resources\SessionDepartemtnResource\Pages\StartSessionDepartemtn;
use App\Models\AgandesTopicForm;
use App\Models\CollegeCouncil;
use App\Models\Department;
use App\Models\Department_Council;
use App\Models\FacultySessionEmail;
use App\Models\Session;
use App\Models\SessionDepartemtn;
use App\Models\SessionTopic;
use App\Models\SessionUser;
use App\Models\Topic;
use App\Models\TopicAgenda;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\SessionEmail;
use App\Models\Faculty;
use App\Models\FacultyCouncil;
use App\Models\FacultySessionTopic;
use App\Models\FacultySessionUser;
use Filament\Notifications\Notification;
use App\Models\YearlyCalendar;
use Filament\Tables\Filters\Filter;

class FacultySessionResource extends Resource
{
    protected static ?string $model = FacultySession::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    public static ?string $label = 'Faculties Sessions';
    protected static ?int $navigationSort = 7;



    public static function form(Form $form): Form
    {
        $users = User::all()->pluck('name', 'id'); // Replace with your logic to fetch users

        return $form
            ->schema([
                Hidden::make('id'),
                Hidden::make('currentSession'),
                Hidden::make('created_by')
                    ->default(fn() => Auth::user()->id),
                Forms\Components\Hidden::make('responsible_id'),

                Forms\Components\Hidden::make('faculty_id')
                    ->default(auth()->user()->faculty_id),

                Wizard::make([
                    Wizard\Step::make('Topics from faculty')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\Select::make('topicsFromFaculty')
                                ->options(function (callable $get, string $operation) {
                                    $facultyId = $get('faculty_id');
                                    $facultySessionId = $get('id');
                                    $filteredAgendaIds = [];

                                    if (!$facultyId) {
                                        return [];
                                    }

                                    $agendas = TopicAgenda::where('topics_agendas.faculty_id', $facultyId)
                                        ->where('topics_agendas.status', 1)
                                        ->where('topics_agendas.classification_reference', 3) // faculty reference
                                        ->where('topics_agendas.escalation_authority', 2) // refer to college
                                        ->leftJoin('faculty_session_topics as session_topic', 'session_topic.topic_agenda_id', '=', 'topics_agendas.id')
                                        ->pluck('topics_agendas.id')->toArray();
                                    // ->pluck('topics_agendas.name', 'topics_agendas.id');

                                    $currentSessionAgendas = FacultySessionTopic::where('faculty_session_topics.faculty_session_id', $facultySessionId)
                                        ->join('topics_agendas as agendas', 'agendas.id', '=', 'faculty_session_topics.topic_agenda_id')
                                        ->where('agendas.classification_reference', 3) // don't call reference college
                                        ->pluck('faculty_session_topics.topic_agenda_id')->toArray();

                                    $existingSessionTopicIds = FacultySessionTopic::whereIn('faculty_session_topics.topic_agenda_id', $agendas)
                                        ->join('topics_agendas as agendas', 'agendas.id', '=', 'faculty_session_topics.topic_agenda_id')
                                        ->where('agendas.classification_reference', 3) // don't call reference college
                                        ->pluck('faculty_session_topics.topic_agenda_id')->toArray();


                                    if ($operation == 'edit') {
                                        // Merge current topics IDs with the difference of agenda IDs and existing session topic IDs
                                        $filteredAgendaIds = array_merge($currentSessionAgendas, array_diff($agendas, $existingSessionTopicIds));
                                    } else {
                                        $filteredAgendaIds = collect($agendas)->diff($existingSessionTopicIds)->toArray();
                                    }

                                    $AgendaNames = self::initializeFacultyTopicsWithoutDecision($filteredAgendaIds);

                                    // Strip HTML tags from each value while preserving the keys
                                    $cleanedAgendaNames = collect($AgendaNames)->mapWithKeys(function ($value, $key) {
                                        $nameOfAgenda = TopicAgenda::where('id', $key)->value('name');

                                        // Split the name of agenda into two parts
                                        [$beforeColon, $afterSlash] = [
                                            strstr($nameOfAgenda, ':', true), // Part before the colon
                                            trim(substr(strstr($nameOfAgenda, '/'), 1)), // Part after the slash
                                        ];

                                        return [$key => $beforeColon . ' : ' . strip_tags($value) . ' / ' . $afterSlash];
                                    })->toArray();

                                    // return $AgendaNames->isEmpty() ? [] : $AgendaNames;
                                    return $cleanedAgendaNames ?? [];
                                })
                                ->label(__('Topic'))
                                ->searchable()
                                ->multiple()
                                ->preload()
                                // ->required()
                                ->validationMessages([
                                    // 'required' => __('required validation'),
                                ])
                                ->live()
                                ->reactive(),
                        ]),

                    Wizard\Step::make('Topic')
                        ->label(__('Topics from departments'))
                        ->schema([
                            // Forms\Components\Select::make('session_way')
                            //     ->translateLabel()
                            //     ->options([
                            //         1 => __('Topics from departments councils sessions'),
                            //         2 => __('Topics in general'),
                            //     ])
                            //     ->required()
                            //     ->validationMessages([
                            //         'required' => __('required validation'),
                            //     ])
                            //     ->translateLabel()
                            //     ->reactive()
                            //     ->native(false)
                            //     ->afterStateUpdated(fn (callable $set) => $set('department_id', null) & $set('session', null) & $set('TopicAgendaId', null))
                            //     ->live(),

                            Forms\Components\Select::make('department_id')
                                ->label(__('Department'))
                                ->options(function (callable $get) {
                                    if ($get('faculty_id')) {
                                        $faculty_id = $get('faculty_id');
                                    }
                                    $allDepartments = Department::where('faculty_id', $faculty_id)->pluck('id')->toArray();

                                    $sessionDepartmentsIds = Session::whereIn('department_id', $allDepartments)->pluck('department_id')->toArray();
                                    $sessionDepartmentsIds = array_unique($sessionDepartmentsIds);

                                    $sessionDepartmentsNames = Department::whereIn('id', $sessionDepartmentsIds)->pluck(self::getDepartmentName())->toArray();

                                    // التحقق من أن $sessionDepartmentsNames ليست null
                                    if (empty($sessionDepartmentsNames)) {
                                        return [];
                                    }

                                    $sessionDepartments = array_combine($sessionDepartmentsIds, $sessionDepartmentsNames);

                                    return $sessionDepartments;
                                })
                                ->multiple()
                                ->required(fn(callable $get) => empty($get('topicsFromFaculty')))
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ])
                                ->translateLabel()
                                ->reactive()
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(fn(callable $set) => $set('session', null) & $set('TopicAgendaId', null)),

                            Forms\Components\Select::make('session')
                                ->label(__('Session'))
                                ->options(function (callable $get) {
                                    $department_ids = $get('department_id');

                                    // التحقق من أن $department_ids ليست null
                                    if (empty($department_ids)) {
                                        return [];
                                    }

                                    $allSessionRelatedToDepartment = Session::whereIn('department_id', $department_ids)
                                        ->where('status', 1)
                                        ->pluck('id')->toArray();

                                    $sessionsIds = CollegeCouncil::where('status', 1)
                                        ->whereIn('session_id', $allSessionRelatedToDepartment)
                                        ->pluck('session_id');

                                    $sessionCodes = Session::whereIn('id', $sessionsIds)
                                        ->orderBy('created_at', 'desc')
                                        ->pluck('code', 'id')
                                        ->toArray();

                                    $mappedSessionCodes = [];

                                    foreach ($sessionCodes as $id => $code) {
                                        $sessionOrderValue = Session::where('id', $id)->value('order');
                                        $parts = explode('_', $code);
                                        $yearCode = $parts[0];
                                        $facultyCode = $parts[1];
                                        $sessionOrder = 'الجلسة ' . self::arabicOrdinal($sessionOrderValue);
                                        $yearName = YearlyCalendar::where('code', $yearCode)->value('name');
                                        $facultyArName = Department::where('code', $facultyCode)->value('ar_name');
                                        $newSessionCode = "{$yearName}_{$facultyArName}_{$sessionOrder}";
                                        $mappedSessionCodes[$id] = $newSessionCode;
                                    }

                                    return $mappedSessionCodes;
                                })
                                ->multiple()
                                ->required(fn(callable $get) => empty($get('topicsFromFaculty')))
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ])
                                ->translateLabel()
                                ->reactive()
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(fn(callable $set) => $set('TopicAgendaId', null)),

                            Forms\Components\Select::make('TopicAgendaId')
                                ->label(__('Topic'))
                                ->options(function (callable $get, string $operation, $record) {
                                    $facultySessionId = $get('id');
                                    $sessionIds = $get('session');

                                    if (empty($sessionIds)) {
                                        return [];
                                    }

                                    $selectedDepartmentId = $get('department_id');
                                    $currentSession = $get('currentSession');

                                    // Fetch currently selected topics
                                    $currentTopicsIds = FacultySessionTopic::where('faculty_session_topics.faculty_session_id', $facultySessionId)
                                        ->join('topics_agendas as agendas', 'agendas.id', '=', 'faculty_session_topics.topic_agenda_id')
                                        ->whereNot('agendas.classification_reference', 3)
                                        ->pluck('faculty_session_topics.topic_agenda_id')->toArray();

                                    // Get available topics from sessions
                                    $agendaIds = CollegeCouncil::whereIn('college_councils.session_id', $sessionIds)
                                        ->whereNotNull('college_councils.topic_id')
                                        ->where('college_councils.escalation_authority', 2)
                                        ->where('college_councils.status', 1)
                                        ->join('topics_agendas as agendas', 'agendas.id', '=', 'college_councils.topic_id')
                                        ->whereNot('agendas.classification_reference', 3)
                                        ->pluck('college_councils.topic_id');

                                    // Ensure removed topics can still be reselected
                                    $existingSessionTopicIds = FacultySessionTopic::whereIn('faculty_session_topics.topic_agenda_id', $agendaIds)
                                        ->join('topics_agendas as agendas', 'agendas.id', '=', 'faculty_session_topics.topic_agenda_id')
                                        ->whereNot('agendas.classification_reference', 3)
                                        ->pluck('faculty_session_topics.topic_agenda_id');

                                    if ($operation == 'edit') {
                                        $agendaIdsArray = $agendaIds->toArray();
                                        $existingSessionTopicIdsArray = $existingSessionTopicIds->toArray();
                                        $existingSessionDepartments = FacultySessionTopic::where('faculty_session_id', $record->id)->pluck('department_id');

                                        $NewcurrentTopicsIds = FacultySessionTopic::where('faculty_session_topics.faculty_session_id', $facultySessionId)
                                        ->whereIn('faculty_session_topics.department_id', $selectedDepartmentId) // Specified the table name
                                        ->join('topics_agendas as agendas', 'agendas.id', '=', 'faculty_session_topics.topic_agenda_id')
                                        ->where('agendas.classification_reference', '!=', 3)
                                        ->pluck('faculty_session_topics.topic_agenda_id')
                                        ->toArray();
                                        if (
                                            collect($existingSessionDepartments)->unique()->intersect(collect($selectedDepartmentId)->unique())->isNotEmpty()
                                            && $currentSession == $sessionIds
                                        ) {
                                            $filteredAgendaIds = array_merge($currentTopicsIds, array_diff($agendaIdsArray, $existingSessionTopicIdsArray));
                                        } elseif (!in_array($currentTopicsIds, $NewcurrentTopicsIds)) {
                                            $commonTopics = array_intersect($currentTopicsIds, $NewcurrentTopicsIds);

                                             $filteredAgendaIds = array_merge($commonTopics, array_diff($agendaIdsArray, $existingSessionTopicIdsArray));
                                              // Fetch topics that are not assigned to the current faculty session
                                        } else {
                                            // Ensure previously selected topics are kept
                                            $filteredAgendaIds = array_merge($currentTopicsIds, array_diff($agendaIdsArray, $existingSessionTopicIdsArray));
                                        }
                                    } else {
                                        // Convert collection to array before merging
                                        $filteredAgendaIds = array_merge($currentTopicsIds, $agendaIds->diff($existingSessionTopicIds)->toArray());
                                    }

                                    $AgendaNames = self::initializeTopicsWithoutDecision($filteredAgendaIds);

                                    $cleanedAgendaNames = collect($AgendaNames)->mapWithKeys(function ($value, $key) {
                                        $nameOfAgenda = TopicAgenda::where('id', $key)->value('name');
                                        [$beforeColon, $afterSlash] = [
                                            strstr($nameOfAgenda, ':', true),
                                            trim(substr(strstr($nameOfAgenda, '/'), 1)),
                                        ];
                                        return [$key => $beforeColon . ' : ' . strip_tags($value) . ' / ' . $afterSlash];
                                    })->toArray();

                                    return $cleanedAgendaNames ?? [];
                                })
                                ->translateLabel()
                                ->searchable()
                                ->multiple()
                                ->required(fn(callable $get) => empty($get('topicsFromFaculty')))
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ])
                                ->live()
                                ->reactive()
                                ->native(false)
                                ->columns(2),


                        ]),

                    Wizard\Step::make('Invitations')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\Select::make('invitations')
                                ->options(function (callable $get) {
                                    $facultyId = $get('faculty_id');

                                    if (!$facultyId) {
                                        return [];
                                    }
                                    // Retrieve the user_ids associated with the faculty_id from the faculty_councils table
                                    $userIds = FacultyCouncil::where('faculty_id', $facultyId)->pluck('user_id');
                                    // $userIds = User::where('faculty_id', $facultyId)->pluck('id'); // retrive user_ids related to faculty

                                    // Retrieve the users where their id is in the retrieved user_ids and return their name and id
                                    $users = User::whereIn('id', $userIds)->pluck('name', 'id')->toArray();

                                    return [0 => 'الكل'] + $users;
                                    /* TODO : i need to add the faculty dean an the secretary as options.*/
                                })
                                ->translateLabel()
                                ->searchable()
                                ->multiple()
                                ->preload()
                                ->required()
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ])
                                ->live()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, Get $get, $record) {
                                    // dump($get('invitations'));
                                    $facultyId = $get('faculty_id');

                                    if (!$facultyId) {
                                        return [];
                                    }
                                    // Retrieve the user_ids associated with the faculty_id from the faculty_councils table
                                    $userIds = FacultyCouncil::where('faculty_id', $facultyId)->pluck('user_id');
                                    // $userIds = User::where('faculty_id', $facultyId)->pluck('id'); // retrive user_ids related to faculty

                                    // Retrieve the users where their id is in the retrieved user_ids and return their name and id
                                    $users = User::whereIn('id', $userIds)->pluck('name', 'id')->toArray();

                                    foreach ($get('invitations') as $option) {
                                        if ($option == 0) {
                                            // dd(array_keys( $users));
                                            $set('invitations', array_keys($users));
                                        }
                                    }
                                })
                            // disable when user is the responsible (head of department) & at edit mode
                            // ->disabled(fn(FacultySession $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->responsible_id)),
                        ]),

                    Wizard\Step::make('Email Invitations')
                        ->translateLabel()
                        ->schema([
                            Repeater::make('email_invitations')
                                ->label('')
                                ->schema([
                                    Forms\Components\Hidden::make('id'),
                                    Forms\Components\Hidden::make('user_id'),
                                    Forms\Components\TextInput::make('name')
                                        ->translateLabel(),
                                    Forms\Components\TextInput::make('email')
                                        ->translateLabel()
                                        ->exists(table: User::class, column: 'email')
                                        ->email()
                                        // ->required()
                                        ->exists(table: User::class, column: 'email')
                                        ->validationMessages([
                                            'exists' => __('exists validation'),
                                        ])

                                ])
                                ->columns(2)
                                // disable when user is the responsible (head of department) & at edit mode
                                ->disabled(fn(Session $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->responsible_id))
                                ->afterStateUpdated(function ($state, $set) {
                                    // Extract only valid emails
                                    $emails = array_column($state, 'email');
                                    $filteredEmails = array_filter($emails, fn($email) => !empty($email));

                                    // Check for duplicate emails
                                    $duplicates = array_filter(array_count_values($filteredEmails), fn($count) => $count > 1);

                                    if ($duplicates) {
                                        $set('email_invitations', collect($state)->map(function ($item) use ($duplicates) {
                                            // If an email is a duplicate, set it to null
                                            if (isset($duplicates[$item['email']])) {
                                                $item['email'] = null;
                                            }
                                            return $item;
                                        })->toArray());

                                        Notification::make()
                                            ->title(__('Duplicate emails found.'))
                                            ->body(__('Please ensure each email is unique.'))
                                            ->warning()
                                            ->send();
                                    }
                                })
                                ->addActionLabel(__('Add another'))
                                ->reactive(),
                            // disable when user is the responsible (head of department) & at edit mode
                            // ->disabled(fn(FacultySession $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->responsible_id))
                        ]),

                    Wizard\Step::make('Time & Place')
                        ->translateLabel()
                        ->schema([
                            DateTimePicker::make('start_time')
                                ->translateLabel()
                                // ->minDate(now()->format('Y-m-d'))
                                ->minDate(now()->subYears(1)->format('Y-m-d')) // minimum years before 1 years before current date
                                ->required()
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ]),
                            // disable when user is the responsible (head of department) & at edit mode
                            // ->disabled(fn(FacultySession $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->responsible_id)),

                            Forms\Components\TextInput::make('total_hours')
                                ->required()
                                ->translateLabel()
                                ->maxLength(255)
                                // ->numeric()
                                ->rules(['numeric', 'min:0']) // Ensure positive hours
                                ->placeholder(__('Enter total hours (numbers only)'))
                                ->validationMessages([
                                    'required' => __('required validation'),
                                    'numeric' => __('numeric validation'),
                                ]),
                            // disable when user is the responsible (head of department) & at edit mode
                            // ->disabled(fn(FacultySession $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->responsible_id)),

                            Forms\Components\TextInput::make('place')
                                ->required()
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ])
                                ->translateLabel()
                                ->maxLength(255),
                            // disable when user is the responsible (head of department) & at edit mode
                            // ->disabled(fn(FacultySession $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->responsible_id)),

                            Hidden::make('scheduled_end_time'),

                        ]),

                    Wizard\Step::make('decision_by')
                        ->label(__('Decision'))
                        ->schema([
                            Forms\Components\Select::make('decision_by')
                                ->label(__('Decision by'))
                                ->required()
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ])
                                ->options([
                                    0 => __('Members'),
                                    1 => __('Secretary of faculty council'),
                                ]),
                            // disable when user is the responsible (head of department) & at edit mode
                            // ->disabled(fn(FacultySession $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->responsible_id)),
                        ]),

                    // Wizard\Step::make('Status')
                    //     ->translateLabel()
                    //     ->schema([
                    //         Forms\Components\Select::make('status')
                    //             ->translateLabel()
                    //             ->options([
                    //                 0 => __('Pending'),
                    //                 1 => __('Accepted'),
                    //                 2 => __('Rejected'),
                    //                 3 => __('Reject with reason'),
                    //             ])
                    //             ->required()
                    //             ->reactive()
                    //             ->disabled(fn(FacultySession $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->created_by)),

                    //         Forms\Components\Textarea::make('reject_reason')
                    //             ->translateLabel()
                    //             ->hidden(fn(Get $get): bool => !($get('status') == 3)) // hidden if status is reject with reason
                    //             ->required()
                    //             // disable when user is the creatour (secertary of department council) & at edit mode
                    //             ->disabled(fn(FacultySession $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->created_by)),


                    //     ])->visibleOn('edit'),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->label(__('Session Number'))
                    // ->rowIndex(),
                    ->getStateUsing(function ($record) {
                        $session = FacultySession::findOrFail($record->id);
                        $sessionOrder = 'الجلسة ' . self::arabicOrdinal($session->order);
                        return $sessionOrder;
                    }),

                Tables\Columns\TextColumn::make('code')
                    ->alignment(Alignment::Center)
                    ->label(__('Code'))
                    ->getStateUsing(function ($record) {
                        $session = FacultySession::findOrFail($record->id);
                        $code = $session->code;

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

                        return $newSessionCode;
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('year.name')
                    ->alignment(Alignment::Center)
                    ->label(__('Academic year')),

                // Tables\Columns\TextColumn::make('topic')
                //     ->label(__('Topic'))
                //     ->alignment('center') // Fixed alignment method call
                //     ->sortable()
                //     ->getStateUsing(function ($record) {
                //         // Get the session agenda IDs associated with the session
                //         $sessionAgendaIds = SessionTopic::where('session_id', $record->id)
                //             ->pluck('topic_agenda_id')
                //             ->toArray();

                //         // Get the agenda names based on the retrieved IDs
                //         $agendaNames = TopicAgenda::whereIn('id', $sessionAgendaIds)
                //             ->pluck('name')
                //             ->toArray();
                //         // Implode the agenda names into a comma-separated string
                //         return implode(', ', $agendaNames);
                //     }),

                // Tables\Columns\TextColumn::make('responsible.name')
                //     ->translateLabel()
                //     ->alignment(Alignment::Center)
                //     ->searchable()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('faculty.ar_name')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->visible(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin'))
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('createdBy.name')
                //     ->translateLabel()
                //     ->alignment(Alignment::Center)
                //     ->searchable()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label(__('Session create date'))
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Count of topics')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->getStateUsing(function ($record) {
                        $sessionId = $record->id;
                        $countOfTopics = FacultySessionTopic::where('faculty_session_id', $sessionId)->count();
                        return $countOfTopics;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('place')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('decision_by')
                //     ->translateLabel()
                //     ->alignment(Alignment::Center)
                //     ->searchable()
                //     // Get the state of the column using a closure
                //     ->getStateUsing(fn ($record) => match ((int) $record->decision_by) {
                //         0 => __('Members'),
                //         1 => __('Secretary of department council'),
                //     })
                //     ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->alignment(Alignment::Center)
                    ->label(__('Status'))
                    ->searchable()
                    ->sortable()
                    // Add a badge to the column
                    ->badge()
                    // Get the state of the column using a closure
                    ->getStateUsing(fn($record) => match ($record->status) {
                        0 => __('Pending'),
                        1 => __('Accepted'),
                        2 => __('Rejected'),
                        3 => __('Reject with reason'),
                    })
                    // Set the color of the badge based on the status
                    ->color(fn($record) => match ($record->status) {
                        0 => 'warning',
                        1 => 'success',
                        2 => 'danger',
                        3 => 'danger',
                    }),

                // Tables\Columns\TextColumn::make('users.name')
                //     ->label(__('Invitations')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                // Filter::make('code')
                //     ->form([
                //         Select::make('Academic year')
                //             ->translateLabel()
                //             ->options(YearlyCalendar::pluck('name', 'code')),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         // dump($data["Academic year"]);
                //         return $query
                //             ->when(
                //                 $data['Academic year'],
                //                 fn(Builder $query, $yearCode): Builder => $query->where('code', 'like', '%' . $yearCode . '%'),
                //             );
                //     }),

                SelectFilter::make('Academic year')
                    ->translateLabel()
                    ->relationship('year', 'name'),

                SelectFilter::make('status')
                    ->translateLabel()
                    ->options([
                        0 => __('Pending'),
                        1 => __('Accepted'),
                        2 => __('Rejected'),
                        3 => __('Reject with reason'),

                    ]),
                SelectFilter::make('faculty')
                    ->translateLabel()
                    ->visible(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin'))
                    ->relationship('faculty', 'ar_name')

            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('startSession')
                        // ->label(function ($record) {
                        //     if (auth()->user()->id == $record->created_by)
                        //         return __('Start Session');
                        //     else
                        //         return __('Enter Session');
                        // })
                        ->label(__('View session'))
                        ->icon('heroicon-o-play')
                        ->url(fn($record) => env('APP_URL') . '/admin/faculty-sessions/' . $record->id . '/start')
                        ->visible(function ($record) {
                            $sessionInvites = FacultySessionUser::where('faculty_session_id', $record->id)->pluck('user_id')->toArray();
                            $sessionEmailsInvites = FacultySessionEmail::where('faculty_session_id', $record->id)->pluck('user_id')->toArray();
                            $session = FacultySession::findOrFail($record->id);
                            $res = $session->responsible_id;
                            $cre = $session->created_by;

                            // Super Admin or System Admin should not see the button unless they are in sessionEmailsInvites
                            if ((auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) && !in_array(auth()->user()->id, $sessionEmailsInvites)) {
                                return false;
                            }

                            if (auth()->user()->id == $cre || auth()->user()->id == $res || in_array(auth()->user()->id, $sessionInvites) || in_array(auth()->user()->id, $sessionEmailsInvites)) {
                                $todayFormatted = Carbon::now()->toDateTimeString();
                                return ($record->start_time <= $todayFormatted && $record->actual_end_time == null && $record->status == 1);
                            }

                            // Allow visibility if the user is in sessionEmailsInvites
                            if (in_array(auth()->user()->id, $sessionEmailsInvites)) {
                                return true;
                            }

                            return false;
                        }),
                    Tables\Actions\ViewAction::make()->label(__('Session details')),
                    Tables\Actions\Action::make('ReportDetails')
                        // ->label('Report Details')
                        ->translateLabel()
                        ->icon('heroicon-o-play')
                        ->visible(function ($record) {
                            return ($record->actual_end_time != null);
                        })
                        ->url(fn($record) => env('APP_URL') . '/admin/faculty-sessions/' . $record->id . '/details-report'),

                    // make it available when the topic approval from the head of departement
                    // Tables\Actions\Action::make('Coverletter')
                    //     ->translateLabel()
                    //     ->icon('heroicon-o-play')
                    //     ->visible(function ($record) {
                    //         return isset($record->sessionDecisions[0]) && $record->sessionDecisions[0]->approval == 1;
                    //     })

                    //     ->action(function ($record) {
                    //         $appURL = env('APP_URL');

                    //         // Build the URL dynamically
                    //         $url = $appURL . '/admin/session-departemtns/' . $record->id . '/session-topics-covel-leter';

                    //         return redirect()->away($url);
                    //     }),

                    Tables\Actions\Action::make('Session Topics')
                        ->color('info')
                        ->label(__('Session Topics'))
                        ->icon('heroicon-o-clipboard-document-check')
                        ->url(fn($record) => env('APP_URL') . '/admin/faculty-sessions/faculty-session-topics/' . $record->id),


                    Tables\Actions\Action::make('AttandenceList')
                        // ->label('Attandence List')
                        ->translateLabel()
                        ->icon('heroicon-o-play')
                        ->visible(function ($record) {
                            return ($record->actual_end_time != null);
                        })
                        ->url(fn($record) => env('APP_URL') . '/admin/faculty-sessions/' . $record->id . '/attandence-list'),
                    Tables\Actions\EditAction::make()->visible(function ($record) {
                        return ($record->created_by == auth()->id()) && !in_array($record->status, [1]);
                    }),

                    Tables\Actions\DeleteAction::make()->visible(function ($record) {
                        return ($record->created_by == auth()->id()) && $record->status != 1;
                    }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            // ->recordUrl(function ($record) {
            //     return null;
            // }) // disable opening edit mode whenr click on row
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->query(function (FacultySession $query) {

                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    // If the user has the role of Super Admin or System Admin, show all sessions
                    return $query;
                }

                if (in_array(auth()->user()->position_id, [4, 5])) {

                    $facultyCouncilId = DB::table('faculty_councils')
                        ->where('user_id', auth()->user()->id)
                        ->pluck('faculty_id')->toArray();

                    $query = FacultySession::whereIn('faculty_id', $facultyCouncilId)->Where(function ($query) {
                        $query->where('status', 1)->orWhere('status', 2)->orWhere('status', 3)->orWhere('status', 0);
                    });
                    return $query;
                } else {
                    $facultyCouncilId = DB::table('faculty_councils')
                        ->where('user_id', auth()->user()->id)
                        ->value('id');

                    $query = FacultySession::where('status', 1)->WhereHas('users', function ($query) {
                        $query->where('users.id', auth()->id());
                    })
                        ->orWhereHas('facultyEmails', function ($query) {
                            $query->where('faculty_sessions.status', 1)->where('faculty_session_emails.user_id', auth()->id());
                        });
                    return $query;
                }
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $sessionId = $infolist->record->id;

        $emailInvitesEmails = FacultySessionEmail::where('faculty_session_id', $sessionId)->pluck('email')->toArray();
        $emailInvitesNames = FacultySessionEmail::where('faculty_session_id', $sessionId)->pluck('name')->toArray();

        $infolist->record['emailInvitesEmails'] = $emailInvitesEmails;
        $infolist->record['emailInvitesNames'] = $emailInvitesNames;

        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        // Components\TextEntry::make('')
                        //     ->label(__('Topic'))
                        //     ->default(function ($record) {
                        //         $sessionUsers = SessionUser::where('session_id', $record->id)->pluck('user_id');
                        //         $sessionTopics = SessionTopic::where('session_id', $record->id)->pluck('topic_agenda_id');
                        //         $topics = TopicAgenda::whereIn('id', $sessionTopics)->pluck('topic_id');
                        //         $topicTitles = Topic::whereIn('id', $topics)->pluck('title');
                        //         return $topicTitles->implode("/\n"); // For new line separation
                        //     }), // Assuming 'id' is the attribute you want to display
                        Components\TextEntry::make('responsible.name')
                            ->translateLabel(),
                        Components\TextEntry::make('createdBy.name')
                            ->translateLabel(),
                        Components\TextEntry::make('place')
                            ->translateLabel(),
                        Components\TextEntry::make('start_time')
                            ->translateLabel(),
                        Components\TextEntry::make('actual_start_time')
                            ->translateLabel(),
                        Components\TextEntry::make('total_hours')
                            ->translateLabel(),
                        Components\TextEntry::make('scheduled_end_time')
                            ->translateLabel(),
                        Components\TextEntry::make('actual_end_time')
                            ->translateLabel(),

                        Components\TextEntry::make('decision_by')
                            ->translateLabel()
                            // ->badge
                            // Get the state of the column using a closure
                            ->getStateUsing(fn($record) => match ((int) $record->decision_by) {
                                0 => __('Members'),
                                1 => __('Secretary of faculty council'),
                            }),
                        Components\TextEntry::make('status')
                            ->translateLabel()
                            ->badge()
                            // Add a badge to the column
                            // Get the state of the column using a closure
                            ->getStateUsing(fn($record) => match ($record->status) {
                                0 => __('Pending'),
                                1 => __('Accepted'),
                                2 => __('Rejected'),
                                3 => __('Reject with reason')
                            })
                            // Set the color of the badge based on the status
                            ->color(fn($record) => match ($record->status) {
                                0 => 'warning',
                                1 => 'success',
                                2 => 'danger',
                                3 => 'danger',
                            }),
                        Components\TextEntry::make('reject_reason')
                            ->label(__('Rejected reason'))
                            ->color('danger')
                            ->visible(fn($record) => $record->status == 2),
                        // ->visible(fn($record) => $record->status == 3),
                        Components\TextEntry::make('users.name')
                            ->label(__('Invitations')),
                        Components\TextEntry::make('emailInvitesEmails')
                            ->label(__('Email Invites')),
                        Components\TextEntry::make('emailInvitesNames')
                            ->label(__('Name Invites')),
                        Components\TextEntry::make('created_at')
                            ->translateLabel(),
                        Components\TextEntry::make('updated_at')
                            ->translateLabel(),
                    ])->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacultySessions::route('/'),
            'create' => Pages\CreateFacultySession::route('/create'),
            'edit' => Pages\EditFacultySession::route('/{record}/edit'),
            'view' => Pages\ViewFacultySession::route('/{record}'),
            'startsession' => Pages\StartFacultySession::route('/{record}/start'),
            'faculty-session-report' => Pages\FacultySessionReport::route('/faculty-session-report/{recordId}'),
            'faculty-details-report' => Pages\FacultyReportDetails::route('/{recordId}/details-report'),
            'faculty-attandence-list' => Pages\FacultyAttandenceList::route('/{recordId}/attandence-list'),
            'faculty-session-topics' => Pages\FacultySessionTopics::route('/faculty-session-topics/{recordId}'),
            'faculty-session-topics-covel-leter' => Pages\CoverletterSessionFaculty::route('/{recordId}/faculty-session-topics-covel-leter'),
            /*
                'index' => Pages\ListSessionDepartemtns::route('/'),
                'create' => Pages\CreateSessionDepartemtn::route('/create'),
                'view' => Pages\ViewSessionDepartemtn::route('/{record}'),
                'startsession' => Pages\StartSessionDepartemtn::route('/{record}/start'),
                'edit' => Pages\EditSessionDepartemtn::route('/{record}/edit'),
                'session-report' => Pages\SessionReport::route('/session-report/{recordId}'),
                'details-report' => Pages\ReportDetails::route('/{recordId}/details-report'),
                'attandence-list' => Pages\AttandenceList::route('/{recordId}/attandence-list'),
                'session-topics' => Pages\SessionTopics::route('/session-topics/{recordId}'),
                'session-topics-covel-leter' => Pages\CoverletterSessionDepartement::route('/{recordId}/session-topics-covel-leter'),
            */
        ];
    }

    public function handleMa7derDetails($recordId)
    {
        // Redirect to the 'ma7der-details' page with the given recordId
        return Pages\FacultySessionReport::route($recordId);
    }

    private static function getFacultyName()
    {
        return app()->getLocale() == 'en' ? 'en_name' : 'ar_name';
    }

    private static function getDepartmentName()
    {
        return app()->getLocale() == 'en' ? 'en_name' : 'ar_name';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Sessions Management');
    }

    public static function getLabel(): ?string
    {
        return __('Faculty Council Session');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Faculties Councils Sessions');
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

    public static function initializeFacultyTopicsWithoutDecision($agendaIds): array
    {
        // $topicFormate = SessionTopic::where('session_topics.session_id', $session->id)
        $topicFormate = TopicAgenda::whereIn('topics_agendas.id', $agendaIds)
            ->join('topics as sub_topic', 'sub_topic.id', '=', 'topics_agendas.topic_id')
            ->join('control_report_faculties as report', 'report.topic_id', '=', 'sub_topic.id')
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
