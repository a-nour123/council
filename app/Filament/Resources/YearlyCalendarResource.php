<?php

namespace App\Filament\Resources;

use App\Filament\Resources\YearlyCalendarResource\Pages;
use App\Filament\Resources\YearlyCalendarResource\RelationManagers;
use App\Models\YearlyCalendar;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;
use App\Forms\Components\UniqueDateRange;
use Alkoumi\LaravelHijriDate\Hijri;
use Carbon\Carbon;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;

class YearlyCalendarResource extends Resource
{
    protected static ?string $model = YearlyCalendar::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('name')
                    ->translateLabel()
                    ->required()
                    // ->rules(['regex:/^(?=.*[\p{L}\p{Arabic}])[\p{L}\p{Arabic}0-9\s!@#$%^&*()-_=+`~]+$/u'])
                    ->validationMessages([
                        // 'regex' => __('regex validation'),
                        'required' => __('required validation'),
                    ])
                    ->maxLength(255),

                Forms\Components\TextInput::make('code')
                    ->required()
                    ->translateLabel()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => __('unique validation'),
                        'required' => __('required validation'),
                    ])
                    ->maxLength(25),

                Section::make('')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->translateLabel()
                            ->minDate(now()->subYears(1)) // minimum years before 1 years before current date
                            ->displayFormat('d/m/Y')
                            ->before('end_date') // should start date before end ex: from 1/5 to 5/5 not from 5/5 to 1/5
                            ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {
                                $startDate = $get('start_date');
                                $endDate = $get('end_date');

                                return $rule->where('start_date', $startDate)->where('end_date', $endDate);
                            })
                            ->reactive()
                            ->validationMessages([
                                'before' => __('before validation') . __('End date'),
                                'unique' => __('unique validation'),
                                'required' => __('required validation'),
                            ])
                            ->required()
                            // ->afterStateUpdated(fn(callable $set) => $set('start hijri date', )),
                            ->afterStateUpdated(function (callable $set, $state) {
                                $hijriDate = Hijri::DateIndicDigits('d/m/Y', $state);
                                $set('start hijri date', $hijriDate);
                            }),

                        Forms\Components\TextInput::make('start hijri date')
                            ->readOnly()
                            ->label(__('Hijri date')),
                    ])->columns(2),


                Section::make('')
                    ->schema([
                        Forms\Components\DatePicker::make('end_date')
                            ->translateLabel()
                            ->after('start_date')
                            ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {
                                $startDate = $get('start_date');
                                $endDate = $get('end_date');

                                return $rule->where('start_date', $startDate)->where('end_date', $endDate);
                            })
                            ->displayFormat('d/m/Y')
                            ->validationMessages([
                                'after' => __('after validation') . __('Start date'),
                                'unique' => __('unique validation'),
                                'required' => __('required validation'),
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $hijriDate = Hijri::DateIndicDigits('d/m/Y', $state);
                                $set('end hijri date', $hijriDate);
                            }),

                        Forms\Components\TextInput::make('end hijri date')
                            ->readOnly()
                            // ->
                            ->label(__('Hijri date')),
                    ])->columns(2),


                Radio::make('status')
                    ->translateLabel()
                    ->options([
                        '0' => __('Not Active'),
                        '1' => __('Active'),
                    ])
                    ->required()
                    ->validationMessages([
                        'required' => __('The status field is required.'),
                    ])
                    ->visibleOn('edit')
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if ($state == '0') {

                            $set('status', '1');

                            // Show an alert to the user
                            Notification::make()
                                // ->title(__('Sorry'))
                                ->body(__("Sorry can't disable active Yearly Calendar"))
                                ->warning()
                                ->color('warning')
                                ->send();
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('code')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    // ->date()
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        // Ensure start_date is a Carbon instance
                        $gregorianDate = Carbon::parse($record->start_date);

                        // Convert the Gregorian date to Hijri
                        $hijriDate = Hijri::DateIndicDigits('d-m-Y', $gregorianDate->format('d-m-Y'));

                        return $hijriDate . ' / ' . $gregorianDate->format('d-m-Y');
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    // ->date()
                    ->getStateUsing(function ($record) {
                        // Ensure end_date is a Carbon instance
                        $gregorianDate = Carbon::parse($record->end_date);

                        // Convert the Gregorian date to Hijri
                        $hijriDate = Hijri::DateIndicDigits('d-m-Y', $gregorianDate->format('d-m-Y'));

                        return $hijriDate . ' / ' . $gregorianDate->format('d-m-Y');
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        __('Not Active') => 'danger',
                        __('Active') => 'success',
                    })
                    ->getStateUsing(fn($record) => ($record->status == 0) ? __('Not Active') : __('Active')),
                Tables\Columns\TextColumn::make('created_at')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    // ->dateTime()
                    ->getStateUsing(function ($record) {
                        $date = $record->created_at->format('d-m-Y');
                        $higriDate = Hijri::DateIndicDigits('d-m-Y', $date);

                        $lastDate = $higriDate . ' / ' . $date;
                        return $lastDate;
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    // ->dateTime()
                    ->getStateUsing(function ($record) {
                        $date = $record->updated_at->format('d-m-Y');
                        $higriDate = Hijri::DateIndicDigits('d-m-Y', $date);

                        $lastDate = $higriDate . ' / ' . $date;
                        return $lastDate;
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([

                // filteration by year activation
                SelectFilter::make('status')
                    ->translateLabel()
                    ->options([
                        '1' => __('Active'),
                        '0' => __('Not Active')
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListYearlyCalendars::route('/'),
            'create' => Pages\CreateYearlyCalendar::route('/create'),
            'edit' => Pages\EditYearlyCalendar::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return __('Yearly Calendar');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Yearly Calendars');
    }
}
