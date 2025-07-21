<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessionDepartemtnResource\Pages;
use App\Filament\Resources\SessionDepartemtnResource\Pages\SessionReport;
use App\Filament\Resources\SessionDepartemtnResource\Pages\StartSessionDepartemtn;
use App\Filament\Resources\SessionDepartemtnResource\RelationManagers;
use App\Models\AgandesTopicForm;
use App\Models\Department;
use App\Models\Department_Council;
use App\Models\Session;
use App\Models\ControlReport;
use App\Models\CoverLetterReport;
use App\Models\SessionDepartemtn;
use App\Models\SessionTopic;
use App\Models\SessionUser;
use App\Models\Topic;
use App\Models\TopicAgenda;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SessionEmail;
use App\Models\YearlyCalendar;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;
use Filament\Tables\Filters\Filter;

class SessionDepartemtnResource extends Resource
{
    protected static ?string $model = Session::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        $users = User::all()->pluck('name', 'id'); // Replace with your logic to fetch users

        return $form
            ->schema([
                Hidden::make('id'),
                Hidden::make('created_by')
                    ->default(fn() => Auth::user()->id),
                Forms\Components\Hidden::make('responsible_id'),

                Forms\Components\Hidden::make('faculty_id'),
                Forms\Components\Hidden::make('TopicAgendaId'),

                Wizard::make([
                    Wizard\Step::make('Topic')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\Select::make('department_id')
                                ->label(__('Department'))
                                ->options(function ($record) {
                                    // Check if we are in edit mode
                                    if ($record) {
                                        // Edit operation: Fetch the department based on the existing department_id
                                        $department = Department::where('id', $record->department_id)
                                            ->pluck('ar_name', 'id');
                                    } else {
                                        // Insert operation: Fetch the department_id for the logged-in user
                                        $de_id = Department_Council::where('user_id', auth()->user()->id)
                                            ->pluck('department_id');
                                        // Fetch the departments for the user's department_id
                                        $department = Department::whereIn('id', $de_id)
                                            ->pluck('ar_name', 'id');
                                    }
                                    return $department->isEmpty() ? [] : $department;
                                })

                                ->required()
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ])
                                ->translateLabel()
                                // ->preload()
                                ->reactive()
                                ->searchable()
                                ->live()
                                // ->multiple()
                                // disable when user is the responsible (head of department) & at edit mode
                                ->disabled(fn($record) => ($record && $record->exists && auth()->user()->id == $record->responsible_id))
                                ->hidden(fn(Department_Council $councils): bool => ($councils::where('user_id', auth()->user()->id)->where('position_id', 2)->count() == 1)),


                            Forms\Components\Select::make('TopicAgendaId')
                                ->label(__('Topic'))
                                ->options(function (callable $get, $record) {
                                    if ($record) {
                                        // Retrieve the current session ID
                                        $sessionId = $record->id;

                                        // Determine the department ID, either from the request or the authenticated user
                                        $de_id = $get('department_id') ?? Department_Council::where('user_id', auth()->id())->value('department_id');

                                        // Get all agenda IDs for the department with status 1
                                        $agendaIds = TopicAgenda::where('department_id', $de_id)
                                            ->where('status', 1)
                                            ->where('classification_reference', '!=', 3)
                                            ->pluck('id');


                                        // Get the agenda IDs that already exist in session_topics
                                        $existingAgendaIds = SessionTopic::whereIn('topic_agenda_id', $agendaIds)
                                            ->pluck('topic_agenda_id')
                                            ->toArray();

                                        // Filter out the existing agenda IDs from the full list
                                        $filteredAgendaIds = $agendaIds->diff($existingAgendaIds);
                                        // Retrieve agenda IDs that were rejected by the Dean of Faculty and ensure created_at comparison
                                        $returnedAgendaFromDean = DB::table('college_councils')
                                            ->join('session_topics', 'college_councils.topic_id', '=', 'session_topics.topic_agenda_id')
                                            ->whereIn('college_councils.status', [2, 3])
                                            ->whereColumn('college_councils.created_at', '<', 'session_topics.created_at')
                                            ->whereNotIn('college_councils.topic_id', function ($query) use ($sessionId) {
                                                $query->select('topic_agenda_id')
                                                    ->from('session_topics')
                                                    ->where('session_id', '!=', $sessionId);
                                            })
                                            ->pluck('college_councils.topic_id');


                                        // Merge the filtered agenda IDs with those returned by the Dean
                                        $finalAgendaIds = $filteredAgendaIds->merge($returnedAgendaFromDean);

                                        // // Retrieve the names of the final agendas, ensuring uniqueness
                                        // $agendaNames = TopicAgenda::whereIn('id', $finalAgendaIds->unique())
                                        //     ->where('department_id', $de_id)
                                        //     ->pluck('name', 'id');

                                        $AgendaIds = TopicAgenda::whereIn('id', $finalAgendaIds->unique())
                                            ->where('department_id', $de_id)
                                            ->pluck('id');

                                        $AgendaNames = self::initializeTopicsWithoutDecision($AgendaIds);

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
                                    } else {
                                        $de_id = $get('department_id') ?? Department_Council::where('user_id', auth()->user()->id)->value('department_id');
                                        $AgendaIds = TopicAgenda::where('department_id', $de_id)
                                            ->where('status', 1)
                                            ->where('classification_reference', '!=', 3)
                                            ->pluck('id');

                                        $existingAgendaIds = SessionTopic::whereIn('topic_agenda_id', $AgendaIds)->pluck('topic_agenda_id')->toArray();
                                        $filteredAgendaIds = $AgendaIds->diff($existingAgendaIds);

                                        // return agendas which rejected from dean of facullty

                                        // Get topic_ids from college_councils where created_at is greater than in session_topics
                                        $returnedAgenda = DB::table('college_councils')
                                            ->whereNotNull('topic_id')
                                            ->join('session_topics', 'college_councils.topic_id', '=', 'session_topics.topic_agenda_id')
                                            ->whereIn('college_councils.status', [2, 3])
                                            ->select('college_councils.topic_id', DB::raw("
                                            CASE
                                                WHEN college_councils.created_at > session_topics.created_at THEN 'new'
                                                WHEN college_councils.created_at < session_topics.created_at THEN 'old'
                                            END as status_category
                                        "))->get();

                                        $newAgenda = $returnedAgenda->where('status_category', 'new')->pluck('topic_id')->toArray();
                                        $oldAgenda = $returnedAgenda->where('status_category', 'old')->pluck('topic_id')->toArray();

                                        $filteredAgenda = array_diff($newAgenda, $oldAgenda);

                                        $finalAgendasIds = array_merge($filteredAgendaIds->toArray(), $filteredAgenda);

                                        // $AgendaNames = TopicAgenda::whereIn('id', array_unique($finalAgendasIds))
                                        //     ->where('department_id', $de_id)
                                        //     ->pluck('name', 'id');

                                        $AgendaIds = TopicAgenda::whereIn('id', array_unique($finalAgendasIds))
                                            ->where('department_id', $de_id)
                                            ->pluck('id');

                                        $AgendaNames = self::initializeTopicsWithoutDecision($AgendaIds);

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
                                    }
                                })
                                // disable when user is the responsible (head of department) & at edit mode
                                ->disabled(fn(Session $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->responsible_id))
                                ->required()
                                ->validationMessages([
                                    // 'required' => __(':attribute is required'),
                                    'required' => __('required validation'),
                                ])
                                ->searchable()
                                ->rules(function (callable $get, $state) {
                                    $rules = [];

                                    $rules[] = function ($attribute, $value, $fail) use ($state) {
                                        // Fetch topic IDs based on selected agenda
                                        $topicIds = TopicAgenda::whereIn('id', $state)->pluck('topic_id')->toArray();

                                        // Count occurrences of each topic_id
                                        $topicIdCounts = array_count_values($topicIds);

                                        // Fetch unique report and cover letter content
                                        $uniqueTopicIds = array_keys($topicIdCounts);
                                        $uniqueTopicReportContent = ControlReport::whereIn('topic_id', $uniqueTopicIds)->pluck('content', 'topic_id')->toArray();
                                        $uniqueTopicCoverLetterContent = CoverLetterReport::whereIn('topic_id', $uniqueTopicIds)->pluck('content', 'topic_id')->toArray();

                                        // Prepare lists for topics with missing/invalid content
                                        $invalidTopics = [
                                            'report' => [],
                                            'coverLetter' => []
                                        ];

                                        $missingTopics = [
                                            'report' => [],
                                            'coverLetter' => []
                                        ];

                                        // Define criteria for invalid content
                                        $invalidContentCriteria = ["", "<p><br></p>"];

                                        // Check report and cover letter content for each topic
                                        foreach ($topicIdCounts as $topicId => $count) {
                                            // Check report content
                                            $reportContent = $uniqueTopicReportContent[$topicId] ?? null;
                                            if (is_null($reportContent)) {
                                                $missingTopics['report'][] = $topicId;
                                            } elseif (in_array($reportContent, $invalidContentCriteria)) {
                                                $invalidTopics['report'][] = $topicId;
                                            }

                                            // Check cover letter content
                                            $coverLetterContent = $uniqueTopicCoverLetterContent[$topicId] ?? null;
                                            if (is_null($coverLetterContent)) {
                                                $missingTopics['coverLetter'][] = $topicId;
                                            } elseif (in_array($coverLetterContent, $invalidContentCriteria)) {
                                                $invalidTopics['coverLetter'][] = $topicId;
                                            }
                                        }

                                        // Fetch topic titles for error messages
                                        $getTopicTitles = function ($topicIds) {
                                            return Topic::whereIn('id', $topicIds)->pluck('title', 'id')->toArray();
                                        };

                                        $errorMessages = [];

                                        // Check for missing or invalid report content
                                        if (!empty($missingTopics['report']) || !empty($invalidTopics['report'])) {
                                            $missingTitles = $getTopicTitles($missingTopics['report']);
                                            $invalidTitles = $getTopicTitles($invalidTopics['report']);

                                            foreach ($missingTitles as $title) {
                                                $errorMessages[] = "{$title} (Missing report content)";
                                            }
                                            foreach ($invalidTitles as $title) {
                                                $errorMessages[] = "{$title} (Invalid report content)";
                                            }

                                            // Send notification for missing/invalid report content
                                            Notification::make()
                                                ->title(__("Some topics don't contain a valid report template"))
                                                ->body(implode('<br>', $errorMessages))
                                                ->icon('heroicon-o-x-circle')
                                                ->danger()
                                                ->color('danger')
                                                ->send();

                                            // Trigger validation failure for report
                                            $fail(__('Each topic must have a valid report template'));
                                        }

                                        // Check for missing or invalid cover letter content
                                        if (!empty($missingTopics['coverLetter']) || !empty($invalidTopics['coverLetter'])) {
                                            $missingTitles = $getTopicTitles($missingTopics['coverLetter']);
                                            $invalidTitles = $getTopicTitles($invalidTopics['coverLetter']);

                                            foreach ($missingTitles as $title) {
                                                $errorMessages[] = "{$title} (Missing cover letter content)";
                                            }
                                            foreach ($invalidTitles as $title) {
                                                $errorMessages[] = "{$title} (Invalid cover letter content)";
                                            }

                                            // Send notification for missing/invalid cover letter content
                                            Notification::make()
                                                ->title(__("Some topics don't contain a valid cover letter template"))
                                                ->body(implode('<br>', $errorMessages))
                                                ->icon('heroicon-o-x-circle')
                                                ->danger()
                                                ->color('danger')
                                                ->send();

                                            // Trigger validation failure for cover letter
                                            $fail(__('Each topic must have a valid cover letter template'));
                                        }
                                    };

                                    return $rules;
                                })





                                ->live()
                                ->multiple(),

                        ]),

                    Wizard\Step::make('Invitations')
                        ->translateLabel()
                        ->schema([
                            // Forms\Components\Select::make('invitations')
                            //     ->options([
                            //         0 => "Check all",
                            //         1 => "IT",
                            //         2 => "CS",
                            //         3 => "AI",
                            //     ])
                            //     ->translateLabel()
                            //     ->searchable()
                            //     ->multiple()
                            //     ->preload()
                            //     ->live()
                            //     ->reactive()
                            //      (function ($state, callable $set) {
                            //         // Check if the "Select All" option is selected
                            //         if (in_array(0, $state)) {
                            //             // If "Select All" is selected, set the state to include all options except "Select All"
                            //             $set('invitations', [1, 2, 3]);
                            //         } elseif ($state === []) {
                            //             // If no options are selected, clear the state
                            //             $set('invitations', []);
                            //         } elseif (count(array_diff($state, [0])) === 0) {
                            //             // If all other options are selected, ensure "Select All" remains selected
                            //             $set('invitations', [0]);
                            //         }
                            //     }),







                            Forms\Components\Select::make('invitations')
                                ->options(function (callable $get, $record) {

                                    $TopicAgendaId = $get('TopicAgendaId');
                                    $departmentId = null;

                                    if (!$TopicAgendaId) {
                                        return [];
                                    }

                                    if ($record) {
                                        $departmentId = TopicAgenda::where('id', $TopicAgendaId)
                                            ->where('department_id', $get('department_id'))
                                            ->value('department_id');
                                        $existingUserIds = SessionUser::where('session_id', $record->id)
                                            ->pluck('user_id')
                                            ->toArray();
                                    } else {
                                        if ($get('department_id')) {
                                            $departmentId = $get('department_id');
                                        } else {
                                            $departmentId = Department_Council::where('user_id', auth()->user()->id)->value('department_id');
                                        }
                                        $departmentId = TopicAgenda::whereIn('id', $TopicAgendaId)
                                            ->where('department_id', $departmentId)
                                            ->value('department_id');

                                        $existingUserIds = [];
                                    }

                                    if (!$departmentId) {
                                        return [];
                                    }

                                    $userIds = Department_Council::where('department_id', $departmentId)
                                        ->pluck('user_id')
                                        ->unique()
                                        ->toArray();
                                    $availableUserIds = array_diff($userIds, $existingUserIds);

                                    $users = User::whereIn('id', $availableUserIds)
                                        ->pluck('name', 'id')  // Ensure names are the values and IDs are the keys
                                        ->toArray();

                                    return [0 => 'الكل'] + $users;

                                    // return User::whereIn('id', $availableUserIds)
                                    //     ->pluck('name', 'id')  // Ensure names are the values and IDs are the keys
                                    //     ->toArray();
                                })
                                ->afterStateUpdated(function ($state, callable $set, Get $get, $record) {
                                    // dump($get('invitations'));
                                    $TopicAgendaId = $get('TopicAgendaId');
                                    $departmentId = null;

                                    if (!$TopicAgendaId) {
                                        return [];
                                    }

                                    if ($record) {
                                        $departmentId = TopicAgenda::where('id', $TopicAgendaId)
                                            ->where('department_id', $get('department_id'))
                                            ->value('department_id');
                                        $existingUserIds = SessionUser::where('session_id', $record->id)
                                            ->pluck('user_id')
                                            ->toArray();
                                    } else {
                                        if ($get('department_id')) {
                                            $departmentId = $get('department_id');
                                        } else {
                                            $departmentId = Department_Council::where('user_id', auth()->user()->id)->value('department_id');
                                        }
                                        $departmentId = TopicAgenda::whereIn('id', $TopicAgendaId)
                                            ->where('department_id', $departmentId)
                                            ->value('department_id');

                                        $existingUserIds = [];
                                    }

                                    if (!$departmentId) {
                                        return [];
                                    }

                                    $userIds = Department_Council::where('department_id', $departmentId)
                                        ->pluck('user_id')
                                        ->unique()
                                        ->toArray();
                                    $availableUserIds = array_diff($userIds, $existingUserIds);

                                    $users = User::whereIn('id', $availableUserIds)
                                        ->pluck('name', 'id')  // Ensure names are the values and IDs are the keys
                                        ->toArray();

                                    foreach ($get('invitations') as $option) {
                                        if ($option == 0) {
                                            // dd(array_keys( $users));
                                            $set('invitations', array_keys($users));
                                        }
                                    }
                                })
                                ->translateLabel()
                                ->searchable()
                                ->multiple()
                                ->preload()
                                ->live()
                                ->reactive()
                                ->required()
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ])
                                // Disable when user is the responsible (head of department) & at edit mode
                                ->disabled(fn(Session $session, string $operation): bool => ($operation === 'edit' && auth()->user()->id == $session->responsible_id)),
                        ]),

                    Step::make('Email Invitations')
                        ->translateLabel()
                        ->schema([
                            Repeater::make('email_invitations')
                                ->label('')
                                ->schema([
                                    Forms\Components\Hidden::make('id'),
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

                        ]),

                    Wizard\Step::make('Time & Place')
                        ->translateLabel()
                        ->schema([
                            DateTimePicker::make('start_time')
                                ->translateLabel()
                                ->required()
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ])
                                // ->minDate(fn ($record) => $record ? now()->subYears(1)->format('Y-m-d') : now()->format('Y-m-d'))
                                // Disable when user is the responsible (head of department) & at edit mode
                                ->disabled(fn(Session $session, string $operation): bool => ($operation === 'edit' && auth()->user()->id == $session->responsible_id)),


                            Forms\Components\TextInput::make('total_hours')
                                ->required()
                                ->validationMessages([
                                    'required' => __('required validation'),
                                    'numeric' => __('numeric validation'),
                                ])
                                ->translateLabel()
                                // ->maxLength(255)
                                // ->numeric()
                                ->rules(['numeric', 'min:0']) // Ensure positive hours
                                ->placeholder(__('Enter total hours (numbers only)'))
                                // disable when user is the responsible (head of department) & at edit mode
                                ->disabled(fn(Session $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->responsible_id)),

                            Forms\Components\TextInput::make('place')
                                ->required()
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ])
                                ->translateLabel()
                                ->maxLength(255)
                                // disable when user is the responsible (head of department) & at edit mode
                                ->disabled(fn(Session $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->responsible_id)),

                            Hidden::make('scheduled_end_time'),

                        ]),

                    Wizard\Step::make('decision_by')
                        ->label(__('Decision'))
                        ->schema([
                            Forms\Components\Select::make('decision_by')
                                ->label(__('Decision by'))
                                ->options([
                                    0 => __('Members'),
                                    1 => __('Secretary of department council'),
                                ])
                                ->required()
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ])
                                // disable when user is the responsible (head of department) & at edit mode
                                ->disabled(fn(Session $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->responsible_id)),
                        ]),

                    // Wizard\Step::make('Status')
                    //     ->translateLabel()
                    //     ->schema([
                    //         Forms\Components\Select::make('status')
                    //             ->translateLabel()
                    //             ->options(function ($get) {
                    //                 $options = [
                    //                     0 => __('Pending'),
                    //                     1 => __('Accepted'),
                    //                     2 => __('Rejected'),
                    //                     3 => __('Reject with reason'),
                    //                 ];

                    //                 // Check if start_time is set and less than now
                    //                 if ($get('start_time') && \Carbon\Carbon::parse($get('start_time'))->isPast()) {
                    //                     unset($options[1]); // Remove 'Accepted' option
                    //                 }

                    //                 return $options;
                    //             })
                    //             ->required()
                    //             ->validationMessages([
                    //                 'required' => __('required validation'),
                    //             ])
                    //             ->reactive()
                    //             ->disabled(fn(Session $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->created_by)),

                    //         Forms\Components\Textarea::make('reject_reason')
                    //             ->translateLabel()
                    //             ->hidden(fn(Get $get): bool => !($get('status') == 3)) // hidden if status is reject with reason
                    //             ->required()
                    //             // disable when user is the creatour (secertary of department council) & at edit mode
                    //             ->disabled(fn(Session $session, string $operation): bool => ($operation == 'edit' && auth()->user()->id == $session->created_by)),


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
                        $session = Session::findOrFail($record->id);
                        $sessionOrder = 'الجلسة ' . self::arabicOrdinal($session->order);
                        return $sessionOrder;
                    }),

                Tables\Columns\TextColumn::make('code')
                    ->alignment(Alignment::Center)
                    ->label(__('Code'))
                    ->getStateUsing(function ($record) {
                        $session = Session::findOrFail($record->id);
                        $code = $session->code;

                        // Split the code into parts using "_"
                        $parts = explode('_', $code);

                        // Assign the parts to variables
                        $yearCode = $parts[0]; // Before the first "_"
                        $departmentCode = $parts[1]; // Between the first and second "_"
                        // $lastPart = $parts[2]; // After the second "_"
                        $sessionOrder = 'الجلسة ' . self::arabicOrdinal($session->order);
                        $yearName = YearlyCalendar::where('code', $yearCode)->value('name');
                        $departmentArName = Department::where('code', $departmentCode)->value('ar_name');

                        // $newSessionCode = "{$yearName}_{$departmentArName}_{$lastPart}";
                        $newSessionCode = "{$yearCode}_{$departmentCode}_{$sessionOrder}";

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
                Tables\Columns\TextColumn::make('department.ar_name')
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
                        $countOfTopics = SessionTopic::where('session_id', $sessionId)->count();
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
                    // Only show this column if the user's position ID is not 5
                    ->visible(auth()->user()->position_id != 5)
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
                SelectFilter::make('department')
                    ->translateLabel()
                    ->visible(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin'))
                    ->relationship('department', 'ar_name')

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
                        ->url(fn($record) => env('APP_URL') . '/admin/session-departemtns/' . $record->id . '/start')
                        ->visible(function ($record) {
                            $sessionInvites = SessionUser::where('session_id', $record->id)->pluck('user_id')->toArray();
                            $sessionEmailsInvites = SessionEmail::where('session_id', $record->id)->pluck('user_id')->toArray();
                            $session = Session::findOrFail($record->id);
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
                        ->url(fn($record) => env('APP_URL') . '/admin/session-departemtns/' . $record->id . '/details-report'),

                    Tables\Actions\Action::make('Session Topics')
                        ->color('info')
                        ->label(__('Session Topics'))
                        ->icon('heroicon-o-clipboard-document-check')
                        ->url(fn($record) => env('APP_URL') . '/admin/session-departemtns/session-topics/' . $record->id),


                    Tables\Actions\Action::make('AttandenceList')
                        // ->label('Attandence List')
                        ->translateLabel()
                        ->icon('heroicon-o-play')
                        ->visible(function ($record) {
                            return ($record->actual_end_time != null);
                        })
                        ->url(fn($record) =>  env('APP_URL') . '/admin/session-departemtns/' . $record->id . '/attandence-list'),

                    Tables\Actions\EditAction::make()->visible(function ($record) {
                        return ($record->created_by == auth()->id()) && !in_array($record->status, [1]);
                    }),

                    Tables\Actions\DeleteAction::make()->visible(function ($record) {
                        return ($record->created_by == auth()->id()) && $record->status != 1;
                    }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->query(function (Session $query) {
                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    // If the user has the role of Super Admin or System Admin, show all sessions
                    return $query; // Specify the 'sessions' table for created_at
                }

                if (in_array(auth()->user()->position_id, [2, 3])) {
                    $departmentCouncilId = DB::table('department__councils')
                        ->where('user_id', auth()->user()->id)
                        ->pluck('department_id')->toArray();

                    $query = Session::whereIn('department_id', $departmentCouncilId)->Where(function ($query) {
                        $query->whereIn('status', [0, 1, 2, 3]);
                    });

                    return $query; // Again, specify 'sessions' table
                } else {
                    $departmentCouncilId = DB::table('department__councils')
                        ->where('user_id', auth()->user()->id)
                        ->value('id');

                    $query = Session::where('status', 1)
                        ->whereHas('users', function ($query) {
                            $query->where('users.id', auth()->id()); // Specify 'sessions' table here
                        })
                        ->orWhereHas('sessionEmails', function ($query) {
                            $query->where('sessions.status', 1)
                                ->where('session_emails.user_id', auth()->id())
                            ; // Specify 'sessions' table here too
                        });

                    return $query; // Final ordering for 'sessions' table
                }
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $sessionId = $infolist->record->id;

        $emailInvitesEmails = SessionEmail::where('session_id', $sessionId)->pluck('email')->toArray();
        $emailInvitesNames = SessionEmail::where('session_id', $sessionId)->pluck('name')->toArray();

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
                                1 => __('Secretary of department council'),
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
    // public static function getEloquentQuery(): Builder
    // {
    //     // Customize who can see this session
    //     return parent::getEloquentQuery()
    //         ->where(function ($query) {
    //             // Sessions where the current user is responsible and the status is 1
    //             $query->where(function ($query) {
    //                 $query->where('responsible_id', Auth::id())
    //                       ->where('status', 1);
    //             })
    //             // Sessions where the current user is invited with a status of 2
    //             ->orWhereHas('users', function ($query) {
    //                 $query->where('users.id', Auth::id())
    //                       ->where('status', 1);
    //             });
    //         });
    // }


    public static function getPages(): array
    {
        return [
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

        ];
    }



    public function handleMa7derDetails($recordId)
    {
        // Redirect to the 'ma7der-details' page with the given recordId
        return Pages\SessionReport::route($recordId);
    }


    public static function getNavigationGroup(): ?string
    {
        return __('Sessions Management');
    }

    public static function getLabel(): ?string
    {
        return __('Department Council Session');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Departments Councils Sessions');
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
