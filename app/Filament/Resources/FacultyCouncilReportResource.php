<?php

namespace App\Filament\Resources;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Filament\Resources\FacultyCouncilReportResource\Pages;
use App\Filament\Resources\FacultyCouncilReportResource\RelationManagers;
use App\Models\Faculty;
use App\Models\FacultySession;
use App\Models\YearlyCalendar;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\DB;

class FacultyCouncilReportResource extends Resource
{
    protected static ?string $model = FacultySession::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 8;

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
                return FacultySession::query()->whereNotNull('actual_end_time');
            })
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

                        // dd(compact('yearCode', 'facultyCode', 'lastPart'));
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


                Tables\Columns\TextColumn::make('faculty.ar_name')
                    ->alignment(Alignment::Center)
                    ->label(__('Faculty'))
                    ->searchable()
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('ReportDetails')
                    // ->label('Report Details')
                    ->translateLabel()
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->visible(function ($record) {
                        return ($record->actual_end_time != null);
                    })
                    ->action(function ($record) {
                        $appURL = env('APP_URL');

                        // Build the URL dynamically
                        $url = $appURL . '/admin/faculty-sessions/' . $record->id . '/details-report';
                        // dd($url);

                        return redirect()->away($url);
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(function ($record) {
                return null;
            }) // disable opening edit mode whenr click on row
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->query(function (FacultySession $query) {
                // display sessions which ended
                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    // If the user has the role of Super Admin or System Admin, show all sessions
                    return $query->whereNotNull('actual_end_time'); // Final ordering for 'sessions' table
                }

                if (in_array(auth()->user()->position_id, [4, 5])) {
                    $facultyCouncilId = DB::table('faculty_councils')
                        ->where('user_id', auth()->user()->id)
                        ->pluck('faculty_id')->toArray();

                    $query = FacultySession::whereIn('faculty_id', $facultyCouncilId)->Where(function ($query) {
                        $query->whereIn('status', [0, 1, 2, 3]);
                    });

                    return $query->whereNotNull('faculty_sessions.actual_end_time')->orderBy('faculty_sessions.created_at', 'desc'); // Again, specify 'sessions' table
                } else {
                    $facultyCouncilId = DB::table('faculty_council')
                        ->where('user_id', auth()->user()->id)
                        ->value('id');

                    $query = FacultySession::where('status', 1)
                        ->whereHas('users', function ($query) {
                            $query->where('users.id', auth()->id())
                                ->orderBy('faculty_sessions.created_at', 'desc'); // Specify 'sessions' table here
                        })
                        ->orWhereHas('sessionEmails', function ($query) {
                            $query->where('faculty_sessions.status', 1)
                                ->where('faculty_session_emails.user_id', auth()->id())
                                ->orderBy('faculty_sessions.created_at', 'desc'); // Specify 'sessions' table here too
                        });

                    // return $query->orderBy('faculty_sessions.created_at', 'desc'); // Final ordering for 'sessions' table
                    return $query->whereNotNull('faculty_sessions.actual_end_time')->orderBy('faculty_sessions.created_at', 'desc'); // Again, specify 'sessions' table
                }
            });
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
            'index' => Pages\ListFacultyCouncilReports::route('/'),
            // 'create' => Pages\CreateFacultyCouncilReport::route('/create'),
            // 'edit' => Pages\EditFacultyCouncilReport::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Sessions Management');
    }

    public static function getLabel(): ?string
    {
        return __('Faculty Council Report');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Faculties Councils Reports');
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
