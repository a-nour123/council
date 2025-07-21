<?php

namespace App\Filament\Resources;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Filament\Resources\SessionDepartmentReportResource\Pages;
use App\Filament\Resources\SessionDepartmentReportResource\RelationManagers;
use App\Models\CollegeCouncil;
use App\Models\Department;
use App\Models\Session;
use App\Models\YearlyCalendar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\DB;

class SessionDepartmentReportResource extends Resource
{
    protected static ?string $model = Session::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                // Modify the query here to include rows where topic_id is NULL
                return Session::query()->whereNotNull('actual_end_time');
            })
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

                        // dd(compact('yearCode', 'departmentCode', 'lastPart'));
                        return $newSessionCode;
                    })
                    // ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('year.name')
                    ->alignment(Alignment::Center)
                    ->label(__('Academic year')),

                Tables\Columns\TextColumn::make('actual_end_time')
                    // ->dateTime()
                    ->label(__('Approval time'))
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        // Parse actual_end_time as a DateTime object
                        $date = \Carbon\Carbon::parse($record->actual_end_time)->format('d-m-Y');
                        $higriDate = Hijri::DateIndicDigits('d-m-Y', $date);

                        return $higriDate . ' / ' . $date;
                    }),

                Tables\Columns\TextColumn::make('place')
                    ->alignment(Alignment::Center)
                    ->label(__('Place'))
                    ->searchable()
                    ->sortable(),

                // Tables\Columns\TextColumn::make('Academic year')
                //     ->alignment('center') // `Alignment::Center` can be simplified
                //     ->translateLabel()
                //     ->getStateUsing(function ($record) {
                //         $session = Session::findOrFail($record->id);
                //         $code = $session->code;
                //         // Split the code into parts using "_"
                //         $parts = explode('_', $code);

                //         // Assign the parts to variables
                //         $yearCode = $parts[0]; // Before the first "_"

                //         $yearName = YearlyCalendar::where('code', $yearCode)->value('name');

                //         return $yearName ?? 'غير معروف';
                //     })
                //     ->searchable()
                //     ->sortable(),


                Tables\Columns\TextColumn::make('department.ar_name')
                    ->alignment(Alignment::Center)
                    ->label(__('Department'))
                    ->searchable()
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('Academic year')
                    ->translateLabel()
                    ->relationship('year', 'name'),

                SelectFilter::make('department')
                    ->translateLabel()
                    ->visible(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin'))
                    ->relationship('department', 'ar_name')
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('ReportDetails')
                    // ->label('Report Details')
                    ->icon('heroicon-o-document-check')
                    ->color('primary')
                    ->translateLabel()
                    // ->icon('heroicon-o-play')
                    ->action(function ($record) {
                        $appURL = env('APP_URL');

                        // Build the URL dynamically
                        $url = $appURL . '/admin/session-departemtns/' . $record->id . '/details-report';

                        return redirect()->away($url);
                    }),
                Tables\Actions\Action::make('Take decision')
                    ->visible(auth()->user()->position_id == 5) // when user postion is dean of college
                    ->hidden(fn($record) => CollegeCouncil::where('session_id', $record->id)->exists())
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->translateLabel()
                    ->action(function ($record) {
                        $appURL = env('APP_URL');

                        // Build the URL dynamically
                        $url = $appURL . '/admin/session-department-reports/college-council-decision/' . $record->id;

                        return redirect()->away($url);
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->query(function (Session $query) {
                // display sessions which ended
                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    // If the user has the role of Super Admin or System Admin, show all sessions
                    return $query->whereNotNull('actual_end_time'); // Final ordering for 'sessions' table
                }

                if (in_array(auth()->user()->position_id, [2, 3])) {
                    $departmentCouncilId = DB::table('department__councils')
                        ->where('user_id', auth()->user()->id)
                        ->pluck('department_id')->toArray();

                    $query = Session::whereIn('department_id', $departmentCouncilId)->Where(function ($query) {
                        $query->whereIn('status', [0, 1, 2, 3]);
                    });

                    return $query->whereNotNull('sessions.actual_end_time')->orderBy('sessions.created_at', 'desc'); // Again, specify 'sessions' table
                } else if (in_array(auth()->user()->position_id, [5])) {

                    $departmentIdsFromFacultyCouncil = DB::table('faculty_councils')
                        ->where('faculty_councils.user_id', auth()->user()->id)
                        ->where('faculty_councils.position_id', 5)
                        ->join('departments', 'departments.faculty_id', '=', 'faculty_councils.faculty_id')
                        ->pluck('departments.id as department_id')
                        ->toArray();

                    $query = Session::whereIn('department_id', $departmentIdsFromFacultyCouncil)->Where(function ($query) {
                        $query->whereIn('status', [0, 1, 2, 3]);
                    });

                    return $query->whereNotNull('sessions.actual_end_time')->orderBy('sessions.created_at', 'desc'); // Again, specify 'sessions' table

                } else {
                    $departmentCouncilId = DB::table('department__councils')
                        ->where('user_id', auth()->user()->id)
                        ->value('id');

                    $query = Session::where('status', 1)
                        ->whereHas('users', function ($query) {
                            $query->where('users.id', auth()->id())
                                ->orderBy('sessions.created_at', 'desc'); // Specify 'sessions' table here
                        })
                        ->orWhereHas('sessionEmails', function ($query) {
                            $query->where('sessions.status', 1)
                                ->where('session_emails.user_id', auth()->id())
                                ->orderBy('sessions.created_at', 'desc'); // Specify 'sessions' table here too
                        });

                    // return $query->orderBy('sessions.created_at', 'desc'); // Final ordering for 'sessions' table
                    return $query->whereNotNull('sessions.actual_end_time')->orderBy('sessions.created_at', 'desc'); // Again, specify 'sessions' table
                }
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSessionDepartmentReports::route('/'),
            'college-council-decision' => Pages\TakeDecision::route('/college-council-decision/{recordId}'),
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
}
