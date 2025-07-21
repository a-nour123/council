<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacultyResource\Pages;
use App\Filament\Resources\FacultyResource\RelationManagers;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Headquarter;
use App\Models\Position;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Model;

class FacultyResource extends Resource
{
    protected static ?string $model = Faculty::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 1;
    protected static int $globalSearchResultsLimit = 5;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Hidden::make('id'),

                Forms\Components\TextInput::make('ar_name')
                    ->required()
                    ->label(__('Arabic Name'))
                    // make field unique in same headquarter but accept current value on update
                    // ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {

                    //     $faculty_ArName = $get('ar_name'); // ar_name input
                    //     $faculty_HeadquarterId = $get('headquarter_id'); // headquarter input

                    //     $faculty_Exist_ArName = DB::table('faculties')->where('ar_name', $faculty_ArName)
                    //         ->where('headquarter_id', $faculty_HeadquarterId)
                    //         ->first()->ar_name ?? $faculty_ArName;

                    //     $faculty_Exist_HeadquarterId = DB::table('faculties')->where('ar_name', $faculty_ArName)
                    //         ->where('headquarter_id', $faculty_HeadquarterId)
                    //         ->first()->headquarter_id ?? $faculty_HeadquarterId;

                    //     return $rule->where('ar_name', $faculty_Exist_ArName)->where('headquarter_id', $faculty_Exist_HeadquarterId);
                    // })
                    ->unique(ignoreRecord: true)
                    /*
                        let field not accept just numbers or just special characters or english letters just arabic letters
                        or arabic letters with special characters or numbers not with the both in same time
                    */
                    ->rules(['regex:/^[\p{Arabic}\s]+(?:[\d]+|[!@#$%^&*()\-_=+{}|[\]\\;:",.<>?]+)?$/u'])
                    ->validationMessages([
                        'unique' => __('unique validation'),
                        'regex' => __('regex validation'),
                        'required' => __('required validation'),
                    ])
                    ->maxLength(255),
                Forms\Components\TextInput::make('en_name')
                    ->required()
                    // make field unique in same headquarter but accept current value on update
                    // ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {

                    //     $faculty_EnName = $get('en_name'); // en_name input
                    //     $faculty_HeadquarterId = $get('headquarter_id'); // headquarter input

                    //     $faculty_Exist_EnName = DB::table('faculties')->where('en_name', $faculty_EnName)
                    //         ->where('headquarter_id', $faculty_HeadquarterId)
                    //         ->first()->en_name ?? $faculty_EnName;

                    //     $faculty_Exist_HeadquarterId = DB::table('faculties')->where('en_name', $faculty_EnName)
                    //         ->where('headquarter_id', $faculty_HeadquarterId)
                    //         ->first()->headquarter_id ?? $faculty_HeadquarterId;

                    //     return $rule->where('en_name', $faculty_Exist_EnName)->where('headquarter_id', $faculty_Exist_HeadquarterId);
                    // })
                    ->unique(ignoreRecord: true)
                    ->label(__('English Name'))

                    /*
                        let field not accept just numbers or just special characters or arabic letters just english letters
                        or english letters with special characters or numbers not with the both in same time
                    */
                    ->rules(['regex:/^[a-zA-Z ]+(?:[\d]+|[!@#$%^&*()\-_=+{}|[\]\\;:",.<>?]+)?$/'])
                    ->validationMessages([
                        'unique' => __('unique validation'),
                        'regex' => __('regex validation'),
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

                Forms\Components\Select::make('headquarters')
                    // ->translateLabel()
                    ->label(__('Headquarter'))
                    ->multiple()
                    // ->columnSpanFull()
                    ->options(Headquarter::orderBy('created_at', 'desc')->pluck('name', 'id'))
                    ->required()
                    ->validationMessages([
                        'required' => __('required validation'),
                    ]),

                RichEditor::make('message')
                    ->label(__("Faculty Message"))
                    ->columnSpanFull()
                    ->required()
                    ->validationMessages([
                        'required' => __('required validation'),
                    ])
                    ->disableToolbarButtons([
                        'attachFiles',
                        'link',
                        'codeBlock',
                        'h2',
                        'h3',
                    ]),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ar_name')
                    ->alignment(Alignment::Center)
                    ->label(__('Arabic Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('en_name')
                    ->alignment(Alignment::Center)
                    ->label(__('English Name'))
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('headquarter.name')
                //     ->alignment(Alignment::Center)
                //     ->translateLabel()
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([

                // SelectFilter::make('headquarter')->relationship('headquarter', 'name')->translateLabel()
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                //putting condition to stop delete record if found related data
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Faculty $record) {
                        if ($record->departments()->exists()) {
                            Notification::make()
                                ->danger()
                                ->color('danger')
                                ->title(__('Failed to delete'))
                                ->body(__('Faculty contains on departments related'))
                                ->seconds(10)
                                ->send();

                            // This will halt and cancel the delete action modal.
                            $action->cancel();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(function ($record) {
                return null;
            }) // disable opening edit mode whenr click on row
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                ])

            ])
            ->query(function (Faculty $query) {

                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    return $query;
                } else if (auth()->user()->hasRole('Faculty Admin')) {
                    $query = Faculty::where('id', auth()->user()->faculty_id);
                    return $query;
                } else {
                    $query = Faculty::where('id', auth()->user()->faculty_id);
                    return $query;
                }
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $facultyId = $infolist->record->id;

        $faculty = new Faculty();

        $headquartersIds = $faculty->getHeadquarterIds($facultyId)->toArray();
        $headquarters = Headquarter::whereIn('id', $headquartersIds)->pluck('name');

        $department_EnName = Department::where('faculty_id', $facultyId)->pluck('en_name');
        $department_ArName = Department::where('faculty_id', $facultyId)->pluck('ar_name');
        $userName = User::where('faculty_id', $facultyId)->pluck('name');
        $userPositionIDs = User::where('faculty_id', $facultyId)->pluck('position_id');

        // Handle null values for userPositionID and get corresponding positions
        $userPositions = $userPositionIDs->map(function ($positionID) {
            return $positionID ? Position::where('id', $positionID)->pluck(self::getPositionAndRoleName())->first() : '-';
        });

        $infolist->record['department_EnName'] = $department_EnName;
        $infolist->record['department_ArName'] = $department_ArName;
        $infolist->record['userName'] = $userName;
        $infolist->record['userPosition'] = $userPositions;
        $infolist->record['headquarters'] = $headquarters;

        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\TextEntry::make('code')
                            ->label(__('Code')),
                        Components\TextEntry::make('ar_name')
                            ->label(__('Arabic Name')),
                        Components\TextEntry::make('en_name')
                            ->label(__('English Name')),
                        // Components\TextEntry::make('headquarter.name')
                        //     ->translateLabel(),
                        Components\TextEntry::make('headquarters')
                            ->label(__('Related headquarters'))
                            ->hidden(fn(Faculty $faculty): bool => (!$faculty->headquarters()->exists())),
                        Components\TextEntry::make('created_at')
                            ->translateLabel(),
                        Components\TextEntry::make('updated_at')
                            ->translateLabel(),
                    ])->columns(3),

                Components\Section::make('Departments')
                    ->schema([
                        Components\TextEntry::make('department_EnName')
                            ->label(__('English Name'))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->expandableLimitedList(),

                        Components\TextEntry::make('department_ArName')
                            ->label(__('Arabic Name'))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->expandableLimitedList(),

                    ])->heading(__('Departments'))->columns(2)->collapsed()->hidden(fn(Faculty $faculty): bool => (!$faculty->departments()->exists())),

                Components\Section::make('Faculty Admins')
                    ->heading(__('Admins'))
                    ->schema([
                        Components\TextEntry::make('userName')
                            ->label(__('Name'))
                            ->listWithLineBreaks()
                            // ->bulleted()
                            ->expandableLimitedList(),

                        Components\TextEntry::make('userPosition')
                            ->label(__('Position'))
                            ->listWithLineBreaks()
                            // ->bulleted()
                            ->expandableLimitedList(),

                    ])->translateLabel()->columns(2)->collapsed()
                    // make this section hidden where faculty doesn't containing admins related
                    ->hidden(fn(Faculty $faculty): bool => (!$faculty->users()->exists())),
                // make this section hidden where faculty doesn't containing departments related
                // ->hidden(fn (Faculty $faculty): bool => (!$faculty->departments()->exists())),

                Components\Section::make('Council')
                    ->heading(__('Council'))
                    ->schema([
                        Components\TextEntry::make('facultyDean.user.name')
                            ->translateLabel(),
                        Components\TextEntry::make('secretaryOfFacultyCouncil.user.name')
                            ->translateLabel(),
                        Components\TextEntry::make('FacultyCouncilMembers.user.name')
                            ->translateLabel()
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->limitList(1)
                            ->expandableLimitedList(),
                    ])->columns(3)->collapsed()
                    // make this section hidden where faculty doesn't containing council
                    ->hidden(fn(Faculty $faculty): bool => (!$faculty->council()->exists())),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DepartmentsRelationManager::class,
            RelationManagers\FacultyAdminsRelationManager::class,
            RelationManagers\FacultyCouncilRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaculties::route('/'),
            'create' => Pages\CreateFaculty::route('/create'),
            'edit' => Pages\EditFaculty::route('/{record}/edit'),
        ];
    }
    private static function getPositionAndRoleName()
    {
        return app()->getLocale() == 'en' ? 'name' : 'ar_name';
    }

    public static function getLabel(): ?string
    {
        return __('Faculty');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Faculties');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['ar_name', 'code'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'الأسم' => $record->ar_name,
            'الكود' => $record->code,
        ];
    }
}
