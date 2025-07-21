<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use App\Models\Department_Council;
use App\Models\Faculty;
use App\Models\Headquarter;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Model;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 3;

    protected static int $globalSearchResultsLimit = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([

                        Forms\Components\Hidden::make('id'),

                        Forms\Components\TextInput::make('ar_name')
                            ->required()
                            ->label(__('Arabic Name'))
                            // make field unique in same Faculty but accept in another one
                            ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {

                                $department_ArName = $get('ar_name'); // ar_name input
                                $department_FacultyId = $get('faculty_id'); // faculty input

                                $department_Exist_ArName = DB::table('departments')->where('ar_name', $department_ArName)
                                    ->where('faculty_id', $department_FacultyId)
                                    ->first()->ar_name ?? $department_ArName;

                                $department_Exist_FacultyId = DB::table('departments')->where('ar_name', $department_ArName)
                                    ->where('faculty_id', $department_FacultyId)
                                    ->first()->faculty_id ?? $department_FacultyId;

                                return $rule->where('ar_name', $department_Exist_ArName)->where('faculty_id', $department_Exist_FacultyId);
                            })
                            /*
                                let field not accept just numbers or just special characters or english letters but allow
                                just arabic letters or arabic letters with special characters or numbers or with the both in same time
                            */
                            ->rules(['regex:/^(?![a-zA-Z])(?=.*[\p{Arabic}])[\p{Arabic}\d\s!@#$%^&*()\-_=+{}|[\]\\;:",.<>?]+$/u'])
                            ->validationMessages([
                                'unique' => __('unique validation'),
                                'regex' => __('regex validation'),
                                'required' => __('required validation'),
                            ])
                            ->maxLength(255),
                        Forms\Components\TextInput::make('en_name')
                            ->required()
                            ->label(__('English Name'))
                            // make field unique in same Faculty but accept in another one
                            ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {

                                $department_EnName = $get('en_name'); // en_name input
                                $department_FacultyId = $get('faculty_id'); // faculty input

                                $department_Exist_EnName = DB::table('departments')->where('en_name', $department_EnName)
                                    ->where('faculty_id', $department_FacultyId)
                                    ->first()->en_name ?? $department_EnName;

                                $department_Exist_FacultyId = DB::table('departments')->where('en_name', $department_EnName)
                                    ->where('faculty_id', $department_FacultyId)
                                    ->first()->faculty_id ?? $department_FacultyId;

                                return $rule->where('en_name', $department_Exist_EnName)->where('faculty_id', $department_Exist_FacultyId);
                            })
                            /*
                                let field not accept just numbers or just special characters or arabic letters but allow
                                just english letters or english letters with special characters or numbers or with the both in same time
                            */
                            ->rules(['regex:/^(?![\p{Arabic}])(?=.*[a-zA-Z])[a-zA-Z\d\s!@#$%^&*()\-_=+{}|[\]\\;:",.<>?]+$/u'])
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
                        // Forms\Components\Select::make('Headquarter')
                        //     ->options(Headquarter::all()->pluck('name','id')->toArray())
                        //     ->reactive()
                        //     ->translateLabel()
                        //     ->required()
                        //     ->hidden(fn() : bool => (auth()->user()->hasRole('Faculty Admin')))
                        //     ->validationMessages([
                        //         'required' => __('required validation'),
                        //     ])
                        //     ->afterStateUpdated(fn(callable $set) => $set('faculty_id',null)),

                        Forms\Components\Select::make('faculty_id')->label(__('Faculty'))
                            // ->options(function(callable $get){
                            //     $headquarter = Headquarter::find($get('Headquarter'));
                            //     if(!$headquarter){
                            //         return null;
                            //     }
                            //     $faculties = Faculty::where('headquarter_id',$headquarter->id)->pluck(self::getFacultyName(),'id')->toArray();
                            //     return $faculties;
                            // })
                            ->options(Faculty::all()->pluck(self::getFacultyName(), 'id'))
                            // ->disabled(fn(Get $get, string $operation) : bool => ($operation == 'create' && ! filled($get('Headquarter')))) // enable when choose headquarter
                            ->hidden(fn(): bool => (auth()->user()->hasRole('Faculty Admin')))
                            ->reactive()
                            ->native(false)
                            ->required()
                            ->validationMessages([
                                'required' => __('required validation'),
                            ]),

                        RichEditor::make('message')
                            ->label(__("Department Message"))
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


                        // Forms\Components\Select::make('faculty_id')
                        //     ->relationship('faculty', self::getFacultyName())
                        //     ->translateLabel()
                        //     ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Start building the query to retrieve departments
        $departmentsQuery = Department::query();

        // If the authenticated user has the 'Faculty Admin' role
        if (auth()->user()->hasRole('Faculty Admin')) {
            // Filter departments based on the authenticated user's faculty ID
            $departmentsQuery->where('faculty_id', auth()->user()->faculty_id);
        }

        return $table
            // Set the query to the modified departments query
            ->query($departmentsQuery)

            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('code')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable()
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('ar_name')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->label(__('Arabic Name')),
                Tables\Columns\TextColumn::make('en_name')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->label(__('English Name')),
                Tables\Columns\TextColumn::make('faculty.' . self::getFacultyName())
                    ->alignment(Alignment::Center)
                    ->numeric()
                    ->translateLabel()
                    ->searchable()
                    ->hidden(fn(): bool => (auth()->user()->hasRole('Faculty Admin')))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->alignment(Alignment::Center)
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->translateLabel()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->alignment(Alignment::Center)
                    ->dateTime()
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('Faculty')
                    ->translateLabel()
                    ->relationship('faculty', self::getFacultyName())
                    ->hidden(fn(): bool => (auth()->user()->hasRole('Faculty Admin'))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                //putting condition to stop delete record if found related data
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Department $record) {
                        if ($record->council()->exists()) {
                            Notification::make()
                                ->danger()
                                ->color('danger')
                                ->title(__('Failed to delete'))
                                ->body(__('Department contains on council related'))
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->query(function (Department $query) {

                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    return $query;
                } else if (auth()->user()->hasRole('Faculty Admin')) {
                    $query = Department::where('faculty_id', auth()->user()->faculty_id);
                    return $query;
                } else {
                    $query = Department::where('id', auth()->user()->department_id);
                    return $query;
                }
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $faculty = new Faculty;

        $facultyId = $infolist->record->faculty_id;

        // $headquarterId = $faculty->getHeadquarterId($facultyId);

        // $headquarterName = Headquarter::where('id',$headquarterId)->value('name');

        // $infolist->record['Headquarter'] = $headquarterName;

        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\TextEntry::make('code')
                            ->translateLabel(),
                        Components\TextEntry::make('ar_name')
                            ->label(__('Arabic Name')),
                        Components\TextEntry::make('en_name')
                            ->label(__('English Name')),
                        // Components\TextEntry::make('Headquarter')
                        //     ->translateLabel()
                        //     ->hidden(fn() : bool => (auth()->user()->hasRole('Faculty Admin'))),
                        Components\TextEntry::make('faculty.' . self::getFacultyName())
                            ->translateLabel()
                            ->hidden(fn(): bool => (auth()->user()->hasRole('Faculty Admin'))),
                        Components\TextEntry::make('created_at')
                            ->translateLabel(),
                        Components\TextEntry::make('updated_at')
                            ->translateLabel(),
                    ])->columns(3),

                Components\Section::make('Council')
                    ->heading(__('Council'))
                    ->schema([
                        Components\TextEntry::make('HeadOfDepartment.user.name')
                            ->translateLabel(),
                        Components\TextEntry::make('SecretaryOfDepartmentCouncil.user.name')
                            ->translateLabel(),
                        Components\TextEntry::make('DepartmentCouncilMembers.user.name')
                            ->translateLabel()
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->limitList(1)
                            ->expandableLimitedList(),

                    ])->columns(3)->collapsed()
                    // make this section hidden where department doesn't containing council
                    ->hidden(fn(Department $department): bool => (!$department->council()->exists())),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DepartmentCouncilRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            // 'view' => Pages\ViewDepartment::route('/{record}'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }

    private static function getFacultyName()
    {
        return app()->getLocale() == 'en' ? 'en_name' : 'ar_name';
    }

    public static function getLabel(): ?string
    {
        return __('Department');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Departments');
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
