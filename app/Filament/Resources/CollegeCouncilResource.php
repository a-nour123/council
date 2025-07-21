<?php
/*
namespace App\Filament\Resources;

use App\Filament\Resources\CollegeCouncilResource\Pages;
use App\Filament\Resources\CollegeCouncilResource\RelationManagers;
use App\Models\CollegeCouncil;
use App\Models\Session;
use App\Models\SessionTopic;
use App\Models\YearlyCalendar;
use App\Models\Department;
use App\Models\TopicAgenda;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\SelectFilter;
use function Laravel\Prompts\select;

class CollegeCouncilResource extends Resource
{
    protected static ?string $model = CollegeCouncil::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?int $navigationSort = 7;
    public static ?string $label = 'Minutes of department sessions';


    public static function form(Form $form): Form
    {
        $depId = DB::table('department__councils')
            ->where('user_id', auth()->user()->id)
            ->pluck('department_id')->toArray();

        // dd($depId-);

        return $form

            ->schema([

                Forms\Components\Hidden::make('session_id'),

                Forms\Components\TextInput::make('sessionCode')
                    ->label(__('Code'))
                    ->default(function (Get $get) {
                        $sessionId = $get('session_id');

                        return ($sessionId);
                    })
                    ->readOnly()
                    ->visibleOn('edit'),

                Forms\Components\Select::make('session_id')
                    // ->translateLabel()
                    ->label(__('Session'))
                    ->options(
                        Session::orderBy('created_at', 'desc')
                            ->when($depId, function ($query) use ($depId) {
                                $query->whereIn('department_id', $depId);
                            })
                            ->where('status', 1) // where accepted
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('session_decisions')
                                    ->whereColumn('session_decisions.session_id', 'sessions.id')
                                    ->where('session_decisions.approval', 1);
                            })
                            ->whereNotIn('id', function ($query) {
                                $query->select('session_id')
                                    ->from('college_councils');
                            })
                            // ->whereIn('id', function($query) {
                            //     $query->select('session_id')
                            //         ->from('college_councils')
                            //         ->where('status', 3);
                            // })
                            ->pluck('code', 'id')
                    )
                    ->searchable()
                    ->native(false)
                    ->required()
                    ->visibleOn('create'),

                Forms\Components\Select::make('status')
                    ->translateLabel()
                    ->options([
                        1 => __('Accepted'),
                        2 => __('Rejected'),
                        3 => __('Reject with reason'),
                    ])
                    ->required()
                    ->validationMessages([
                        'required' => __('required validation'),
                    ])
                    ->reactive()
                    ->visibleOn('edit'),

                Forms\Components\Textarea::make('reject_reason')
                    ->translateLabel()
                    ->hidden(fn(Get $get): bool => !($get('status') == 3)) // hidden if status is reject with reason
                    ->required()
                    ->validationMessages([
                        'required' => __('required validation'),
                    ])
                    ->visibleOn('edit')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $depId = DB::table('department__councils')
            ->where('user_id', auth()->user()->id)
            ->pluck('department_id')->toArray();

        return $table
            ->query(function () {
                // Modify the query here to include rows where topic_id is NULL
                return CollegeCouncil::query()->whereNull('topic_id');
            })
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('session.code')
                    ->alignment(Alignment::Center)
                    ->label(__('Code'))
                    ->getStateUsing(function ($record) {
                        $session = Session::findOrFail($record->session_id);
                        $code = $session->code;

                        // Split the code by '_dept_' to get yearCode and the remaining part
                        [$yearCode, $remaining] = explode('_dept_', $code);

                        // Extract departmentCode and last part from the remaining string
                        $departmentCode = 'dept_' . substr($remaining, 0, strpos($remaining, '_')); // 'dept_' + numbers until next '_'
                        $lastPart = substr($remaining, strpos($remaining, '_')); // Last part with underscore and final 3 numbers

                        $yearName = YearlyCalendar::where("code",$yearCode)->value('name');
                        $departmentArName = Department::where("code",$departmentCode)->value('ar_name');
                        $newSessionCode = "{$yearName}_{$departmentArName}{$lastPart}";

                        // dd(compact('yearCode', 'departmentCode', 'lastPart'));
                        return $newSessionCode;
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('session.place')
                    ->alignment(Alignment::Center)
                    ->label(__('Place'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('Academic year')
                    ->alignment('center') // `Alignment::Center` can be simplified
                    ->translateLabel()
                    ->getStateUsing(function ($record) {
                        // Find the session using the correct ID
                        $session = Session::find($record->session_id); // Ensure you have the correct relationship

                        if (!$session) {
                            return 'غير معروف'; // If session not found, return a default value
                        }

                        // Ensure the session code exists and process it
                        $sessionCode = $session->code ?? null;

                        if (!$sessionCode) {
                            return 'غير معروف'; // Return if session code is missing
                        }

                        // Extract the year code from the session code
                        $yearCode = explode('_dept', $sessionCode)[0];

                        // Find the corresponding year in the YearlyCalendar model
                        $yearName = YearlyCalendar::where('code', $yearCode)->value('name');

                        return $yearName ?? 'غير معروف'; // Return the year name or a default value
                    })
                    ->searchable()
                    ->sortable(),

                // Tables\Columns\TextColumn::make('session.responsible.name')
                //     ->alignment(Alignment::Center)
                //     ->label(__('Responsible'))
                //     ->searchable()
                //     ->sortable(),

                Tables\Columns\TextColumn::make('session.department.ar_name')
                    ->alignment(Alignment::Center)
                    ->label(__('Department'))
                    ->searchable()
                    ->sortable(),

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
                    ->getStateUsing(function ($record) {
                        if ($record->status == 0)
                            return __('Pending');
                        else
                            return __('Action has been taken');
                    })
                    // ->getStateUsing(fn($record) => match ($record->status) {
                    //     0 => __('Pending'),
                    //     1 => __('Accepted'),
                    //     2 => __('Rejected'),
                    //     3 => __('Rejected with note'),
                    //     4 => __('Action has been taken'),
                    // })
                    // Set the color of the badge based on the status
                    // ->color(fn($record) => match ($record->status) {
                    //     0 => 'warning',
                    //     1 => 'success',
                    //     2 => 'danger',
                    //     3 => 'danger',
                    //     4 => 'success',
                    // }),
                    ->color(function ($record) {
                        if ($record->status == 0)
                            return 'warning';
                        else
                            return 'success';
                    }),


                Tables\Columns\TextColumn::make('created_at')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


            ])

            ->filters([
                // displaying
                SelectFilter::make('department')
                    // ->relationship('session.department', self::getFacultyOrDepartmentName())
                    ->options(function () {
                        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                            return Department::pluck(self::getFacultyOrDepartmentName(), 'id');
                        } else {
                            $userFacultyId = auth()->user()->faculty_id;

                            $facultyDepartmentIds = DB::table('departments')
                                ->where('faculty_id', $userFacultyId)
                                ->pluck('id')->toArray();

                            $userDepartmentIds = DB::table('department__councils')
                                ->whereIn('department_id', $facultyDepartmentIds)
                                ->where('user_id', auth()->user()->id)
                                ->pluck('department_id')->toArray();

                            return Department::whereIn('id', $userDepartmentIds)->pluck(self::getFacultyOrDepartmentName(), 'id');
                        }
                    })
                    ->translateLabel()
                // ->visible(function () {
                //     if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                //         return true;
                //     } else {
                //         return false;
                //     }
                // }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\ViewAction::make()
                        ->modalHeading("")
                        ->label(__('Details')),
                    Tables\Actions\Action::make('ReportDetails')
                        // ->label('Report Details')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('primary')
                        ->translateLabel()
                        // ->icon('heroicon-o-play')
                        ->visible(function ($record) {
                            return ($record->session->actual_end_time != null);
                        })
                        ->action(function ($record) {
                            // dd($record);
                            $appURL = env('APP_URL');

                            // Build the URL dynamically
                            $url = $appURL . '/admin/session-departemtns/' . $record->session_id . '/details-report';
                            // dd($url);

                            return redirect()->away($url);
                        }),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->action(function ($record) {
                            $sessionId = $record->session_id;

                            CollegeCouncil::where('session_id', $sessionId)->delete();

                            Notification::make()
                                ->title(__('Department council session minute deleted successfully'))
                                ->danger()
                                ->duration(3000)
                                ->send();
                        }),
                ])
            ])
            ->recordUrl(function ($record) {
                return null;
            }) // disable opening edit mode whenr click on row
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    // ->action(function () {
                    //     dd("hi");
                    // }),
                    // ->action(function ($record) {
                    //     $sessionId = $record->session_id;
                    //     dd($sessionId);
                    // }),
                ]),
            ])
            ->query(function (CollegeCouncil $query) use ($depId) {
                $userFacultyId = auth()->user()->faculty_id;

                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    // If the user has the role of Super Admin or System Admin, show all sessions
                    return $query->whereNull('topic_id');
                }

                if (auth()->user()->hasRole('Faculty Admin')) {
                    // If the user has the role of Super Admin or System Admin, show all sessions
                    // dd("sad");
                    $userFacultyId = auth()->user()->faculty_id;
                    // dd($userFacultyId);

                    $facultyDepartmentIds = DB::table('departments')
                        ->where('faculty_id', $userFacultyId)
                        ->pluck('id')->toArray();

                    $sessionIds = DB::table('sessions')
                        ->whereIn('department_id', $facultyDepartmentIds)
                        ->pluck('id')->toArray();

                    // return $query->whereIn('session_id', $sessionIds)->where('topic_id', NULL);
                    return $query->whereIn('session_id', $sessionIds)->whereNull('topic_id');
                    // return $query->whereNull('topic_id');
                }

                // $depId = DB::table('department__councils')
                // ->where('user_id', auth()->user()->id)
                // ->get()->first();
                // dd($depId->department);

                if (auth()->user()->position_id == 5) // if user is dean of college
                {
                    $facultyDepartmentIds = DB::table('departments')
                        ->where('faculty_id', $userFacultyId)
                        ->pluck('id')->toArray();

                    $sessionIds = DB::table('sessions')
                        ->whereIn('department_id', $facultyDepartmentIds)
                        ->pluck('id')->toArray();

                    return $query->whereIn('session_id', $sessionIds)->whereNull('topic_id');
                }

                if (is_null($depId)) {
                    // Handle the case where no department council record is found
                    return $query->where('id', '0');
                } else {
                    $facultyDepartmentIds = DB::table('departments')
                        ->where('faculty_id', $userFacultyId)
                        ->pluck('id')->toArray();

                    $userDepartmentIds = DB::table('department__councils')
                        ->whereIn('department_id', $facultyDepartmentIds)
                        ->where('user_id', auth()->user()->id)
                        ->pluck('department_id')->toArray();

                    $sessionIds = DB::table('sessions')
                        ->whereIn('department_id', $userDepartmentIds)
                        ->pluck('id')->toArray();

                    // return $query->whereIn('session_id', $sessionIds)->where('topic_id', NULL);
                    return $query->whereIn('session_id', $sessionIds)->whereNull('topic_id');
                }
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $sessionId = $infolist->record['session_id'];
        $collegeCouncilData = CollegeCouncil::where('session_id', $sessionId)->whereNotNull('topic_id')->get();

        foreach ($collegeCouncilData as $collegeCouncil) {
            $agendaNames = TopicAgenda::where('id', $collegeCouncil->topic_id)->value('name');
            $parts = explode(' : ', $agendaNames);
            $extractedAgendaName = explode(' / ', $parts[1]);

            $topicData[] = [
                'title' => $extractedAgendaName[0],
                // 'title' => TopicAgenda::where('id', $collegeCouncil->topic_id)->value('name'),
                'status' => $collegeCouncil->status,
                'reject_reason' => $collegeCouncil->reject_reason ?? null
            ];
        }

        foreach ($topicData as $topic) {
            $topicStatuses[] = $topic['status'];
            $topicTitles[] = $topic['title'];
            $topicRejectReasons[] = $topic['reject_reason'];
        }

        $infolist->record['topicTitles'] = $topicTitles;
        $infolist->record['topicStatuses'] = $topicStatuses;
        $infolist->record['topicRejectReasons'] = $topicRejectReasons;

        return $infolist
            ->schema([
                Components\Fieldset::make('Session Information')
                    ->translateLabel()
                    ->schema([
                        Components\TextEntry::make('session.code')
                            ->label(__('Code')),
                        Components\TextEntry::make('session.place')
                            ->label(__('Place')),
                        Components\TextEntry::make('session.responsible.name')
                            ->label(__('Responsible')),
                        Components\TextEntry::make('session.sessionDecisions.decision')
                            ->label(__('Decision')),
                        Components\TextEntry::make('session.createdBy.name')
                            ->label(__('Created by'))
                    ])->columns(2),

                Components\Fieldset::make('Session Topics')
                    ->translateLabel()
                    ->schema([
                        Components\TextEntry::make('topicTitles')
                            ->label(__('Title'))
                            ->getStateUsing(function () use ($topicTitles) {
                                // $stateLabels = [
                                //     0 => __('Pending'),
                                // ];
                                return implode("<br><br>", $topicTitles);
                            })
                            ->html(),

                        Components\TextEntry::make('topicStatuses')
                            ->label(__('Status'))
                            ->getStateUsing(function () use ($topicStatuses, $topicRejectReasons) {
                                $stateLabels = [
                                    0 => __('Pending'),
                                    1 => __('Accepted'),
                                    2 => __('Rejected'),
                                    3 => __('Rejected with reason'),
                                ];

                                $statusTexts = array_map(function ($status) use ($stateLabels) {
                                    return $stateLabels[$status] ?? 'unknown';
                                }, $topicStatuses);

                                $rejectReasonTexts = array_map(function ($rejectReason) {
                                    return $rejectReason ?? Null;
                                }, $topicRejectReasons);

                                return implode("<br><br>", $statusTexts);
                            })
                            ->color(function () use ($sessionId) {
                                $status = CollegeCouncil::where('session_id', $sessionId)
                                    ->whereNotNull('topic_id')
                                    ->pluck('status')
                                    ->first();

                                return match ($status) {
                                    0 => 'warning',
                                    1 => 'success',
                                    2 => 'danger',
                                    3 => 'danger',
                                    default => 'secondary',
                                };
                            })
                            ->html(),

                        Components\TextEntry::make('topicRejectReasons')
                            ->label(__('Rejected reason'))
                            ->getStateUsing(function () use ($topicRejectReasons) {

                                $rejectReasonTexts = array_map(function ($rejectReason) {
                                    return $rejectReason ?? "ﻻ يوجد";
                                }, $topicRejectReasons);

                                return implode("<br><br>", $rejectReasonTexts);
                            })
                            ->color('danger')
                            ->html(),

                    ])->columns(3)

            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SessionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollegeCouncils::route('/'),
            'create' => Pages\CreateCollegeCouncil::route('/create'),
            'edit' => Pages\EditCollegeCouncil::route('/{record}/edit'),
        ];
    }

    private static function getFacultyOrDepartmentName()
    {
        return app()->getLocale() == 'en' ? 'en_name' : 'ar_name';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Sessions Management');
    }

    public static function getLabel(): ?string
    {
        return __('Department council session minute');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Departments councils sessions minutes');
    }
}
*/
