<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Http\Controllers\LDAPController;
use App\Models\AcadimicRank;
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
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static int $globalSearchResultsLimit = 5;

    public static function form(Form $form): Form
    {
        $locale = app()->getLocale(); // Get the current application locale
        return $form
            ->schema([
                Forms\Components\Section::make('User details')
                    ->heading(__('User details'))
                    ->schema([
                        Forms\Components\Hidden::make('id')->disabledOn('create'),
                        Forms\Components\Hidden::make('faculty_id')->disabledOn('create'),

                        // Add type selector - only visible on create
                        Forms\Components\Select::make('type')
                            ->translateLabel()
                            ->options([
                                'internal' => __('internal'),
                                'ldap' => 'LDAP',
                            ])
                            ->default('internal')
                            ->reactive()
                            ->visibleOn('create')
                            ->required(),

                        // Add synchronization button - only visible on create when type is ldap
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('synchronization')
                                ->label(__('synchronization'))
                                ->icon('heroicon-o-arrow-path')
                                ->button()
                                ->color('primary')
                                ->visible(fn(callable $get) => $get('type') === 'ldap')
                                ->hidden(fn(callable $get) => $get('is_synced') == true)
                                ->action(function (callable $get, callable $set) {
                                    // Simulate fetching data from LDAP server
                                    $ldapData = self::getLdapUserData($get('username') ?? '');

                                    if (!$ldapData) {
                                        Notification::make()
                                            ->title(__('User not found in LDAP'))
                                            ->danger()
                                            ->color('danger')
                                            ->send();
                                        return;
                                    }

                                    // Set form values with LDAP data
                                    $set('username', $ldapData['username']);
                                    $set('email', $ldapData['email']);

                                    // Show the hidden inputs
                                    $set('is_synced', true);

                                    Notification::make()
                                        ->title(__('User synchronized successfully'))
                                        ->success()
                                        ->color('success')
                                        ->send();
                                })
                        ])->visible(fn(callable $get) => $get('type') === 'ldap')
                            ->visibleOn('create'),

                        // Hidden flag to track sync status
                        Forms\Components\Hidden::make('is_synced')
                            ->default(false)
                            ->reactive(),

                        TextInput::make('name')
                            ->required()
                            ->hidden(fn(callable $get) => $get('type') === 'ldap' && !$get('is_synced'))
                            ->translateLabel()
                            ->validationMessages([
                                'required' => __('required validation'),
                            ])
                            ->maxLength(255),
                        TextInput::make('ar_name')
                            ->label(__('Arabic name in three parts'))
                            ->required()
                            ->hidden(fn(callable $get) => $get('type') === 'ldap' && !$get('is_synced'))
                            ->rules(['regex:/^([\p{Arabic}\s]+ ){2}[\p{Arabic}\s]+$/u'])
                            ->validationMessages([
                                'regex' => __('regex validation'),
                                'required' => __('required validation'),
                            ])
                            ->placeholder(__('Please enter the arabic name in three parts')),

                        TextInput::make('en_name')
                            ->label(__('ُEnglish name in three parts'))
                            ->required()
                            ->hidden(fn(callable $get) => $get('type') === 'ldap' && !$get('is_synced'))
                            ->regex('/^\w+ \w+ \w+$/')
                            ->placeholder(__('Please enter the name in three parts'))
                            ->validationMessages([
                                'regex' => __('regex validation'),
                                'required' => __('required validation'),
                            ]),

                        Select::make('acadimic_rank_id')
                            ->label(__('Acadimic rank'))
                            ->hidden(fn(callable $get) => $get('type') === 'ldap' && !$get('is_synced'))
                            ->validationMessages([
                                'required' => __('required validation'),
                                'regex' => __('regex validation'),
                            ])
                            ->options(AcadimicRank::pluck(self::getPositionAndRoleName(), 'id'))
                            ->required(),

                        TextInput::make('phone')
                            ->label(__('Phone number'))
                            ->tel()
                            ->hidden(fn(callable $get) => $get('type') === 'ldap' && !$get('is_synced'))
                            ->placeholder(__('Please enter a number starts with 9665 and with 12 digits.'))
                            ->required()
                            ->rules(function (callable $get) {
                                $role = $get('role');
                                $facultyId = $get('faculty_id');
                                $id = $get('id'); // Get the current record ID

                                $rules = ['required', 'regex:/^9665[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/', 'digits:12'];

                                // Check if the role is Faculty Admin
                                if ($role == Role::where('name', 'Faculty Admin')->first()->id) {
                                    // Apply unique constraint on phone + faculty_id combination, ignoring the current record
                                    $rules[] = Rule::unique('users', 'phone')->ignore($id)->where('faculty_id', $facultyId);
                                } else {
                                    // Apply regular unique constraint on phone, ignoring the current record
                                    $rules[] = Rule::unique('users', 'phone')->ignore($id);
                                }

                                return $rules;
                            })
                            ->validationMessages([
                                'regex' => __('regex validation'),
                                'min' => __('min validation') . '12',
                                'max' => __('max validation') . '12',
                                'required' => __('required validation'),
                            ])
                            ->minLength(12)
                            ->maxLength(12),

                        TextInput::make('username')
                            ->label(__('Username'))
                            ->regex('/^(?=.*[a-zA-Z])[a-zA-Z0-9]+$/')
                            ->required()
                            ->readOnly(fn(callable $get) => $get('type') === 'ldap' && $get('is_synced'))
                            ->validationMessages([
                                'regex' => __('regex validation'),
                                'required' => __('required validation'),
                            ]),

                        TextInput::make('email')
                            ->email()
                            ->hidden(fn(callable $get) => $get('type') === 'ldap' && !$get('is_synced'))
                            ->readOnly(fn(callable $get) => $get('type') === 'ldap' && $get('is_synced'))
                            ->rules(function (callable $get) {
                                $role = $get('role');
                                $facultyId = $get('faculty_id');
                                $id = $get('id'); // Get the current record ID

                                $rules = [];

                                // Check if the role is Faculty Admin
                                if ($role == Role::where('name', 'Faculty Admin')->first()->id) {
                                    // Apply unique constraint on email + faculty_id combination, ignoring the current record
                                    $rules[] = Rule::unique('users', 'email')->ignore($id)->where('faculty_id', $facultyId);
                                } else {
                                    // Apply regular unique constraint on email, ignoring the current record
                                    $rules[] = Rule::unique('users', 'email')->ignore($id);
                                }

                                return $rules;
                            })
                            ->validationMessages([
                                'regex' => __('regex validation'),
                                'email' => __('email validation'),
                                'unique' => __('unique validation'),
                                'required' => __('required validation'),
                            ])
                            ->translateLabel()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('password')
                            ->password()
                            ->hidden(fn(callable $get) => $get('type') === 'ldap' && !$get('is_synced'))
                            ->revealable()
                            ->translateLabel()
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->minLength(8)
                            ->validationMessages([
                                'regex' => __('regex validation'),
                                'required' => __('required validation'),
                                'max' => __('max validation') . '255',
                                'min' => __('min validation') . '8',
                            ]),
                        FileUpload::make('signature')
                            ->label(__('Signature'))
                            ->hidden(fn(callable $get) => $get('type') === 'ldap' && !$get('is_synced'))
                            ->required()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif']) // Specify accepted file types
                            ->maxSize(2 * 1024) // Max size in kilobytes
                            ->helperText(__('Upload your signature image. Max size: 2MB. Accepted formats: JPEG, PNG, GIF.'))
                            ->preserveFilenames() // Optionally preserve original filenames
                            ->disk('public') // Specify the disk where the file will be stored
                            ->directory('signatures') // Specify the directory within the disk
                            ->downloadable(),

                    ])->columns(2),

                Forms\Components\Section::make('Related details')
                    ->heading(__('Related details'))
                    ->visible(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin'))
                    ->hidden(fn(callable $get) => ($get('type') === 'ldap' && !$get('is_synced')))
                    ->schema([
                        // Rest of the form remains the same
                        Forms\Components\Select::make('role')
                            ->relationship(name: 'roles', titleAttribute: 'name')
                            ->translateLabel()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->options(function (callable $get) use ($locale) {
                                if ($locale == 'ar') {
                                    // Load roles excluding specific Arabic names
                                    if (auth()->user()->hasRole('Faculty Admin')) {
                                        return Role::whereNotIn('ar_name', ['مسؤول كلية', 'المشرف الأعلى', 'مسؤول النظام'])->pluck('ar_name', 'id');
                                    } else {
                                        return Role::whereNotIn('ar_name', ['المشرف الأعلى'])->pluck('ar_name', 'id');
                                    }
                                } else {
                                    // Default behavior for other languages (assuming English)
                                    if (auth()->user()->hasRole('Faculty Admin')) {
                                        return Role::whereNotIn('name', ['Faculty Admin', 'Super Admin', 'System Admin'])->pluck('name', 'id');
                                    } else {
                                        return Role::whereNotIn('name', ['Super Admin'])->pluck('name', 'id');
                                    }
                                }
                            })
                            ->validationMessages([
                                'required' => __('required validation'),
                            ]),

                        // The rest of the form components remain unchanged
                        Forms\Components\Select::make('position_id')
                            ->translateLabel()
                            ->relationship('position', 'name')
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => $set('hide_faculty_field', $state == 6 || $state == 7))
                            ->native(false)
                            ->options(function (callable $get) use ($locale) {
                                $positions = Position::all();

                                // Filter the position options based on the selected faculty
                                $excludedIds = [6, 7]; // IDs of the positions to be excluded
                                if ($get('faculty_id')) {
                                    $positions = $positions->filter(function ($position) use ($excludedIds) {
                                        return !in_array($position->id, $excludedIds);
                                    });
                                }
                                if ($locale == 'ar')
                                    return $positions->pluck('ar_name', 'id')->toArray();
                                else
                                    return $positions->pluck('name', 'id')->toArray();
                            })
                            ->rules(function (callable $get) {
                                $rules = [];

                                // Get the selected faculty_id and position_id
                                $facultyId = $get('faculty_id');
                                $positionId = $get('position_id');
                                $id = $get('id'); // Get the current record ID

                                // Get the dean and secretary position IDs
                                $deanPositionId = 5;
                                $secretaryPositionId = 4;

                                // Custom validation rule to ensure only one dean per faculty
                                if ($positionId == $deanPositionId) {
                                    $rules[] = function ($attribute, $value, $fail) use ($facultyId, $deanPositionId, $id) {
                                        $existingDean = User::where('faculty_id', $facultyId)
                                            ->where('position_id', $deanPositionId)
                                            ->when($id, fn($query) => $query->where('id', '!=', $id))
                                            ->exists();
                                        if ($existingDean) {
                                            $fail(__('Each faculty can have only one dean.'));
                                        }
                                    };
                                }

                                // Custom validation rule to ensure only one secretary per faculty
                                if ($positionId == $secretaryPositionId) {
                                    $rules[] = function ($attribute, $value, $fail) use ($facultyId, $secretaryPositionId, $id) {
                                        $existingSecretary = User::where('faculty_id', $facultyId)
                                            ->where('position_id', $secretaryPositionId)
                                            ->when($id, fn($query) => $query->where('id', '!=', $id))
                                            ->exists();
                                        if ($existingSecretary) {
                                            $fail(__('Each faculty can have only one secretary.'));
                                        }
                                    };
                                }

                                return $rules;
                            }),

                        // Faculty ID field
                        Forms\Components\Select::make('faculty_id')->label(__('Faculty'))
                            ->translateLabel()
                            ->required(function (callable $get) {
                                return $get('role') == Role::where('name', 'Faculty Admin')->first()->id;
                            })
                            ->hidden(function (callable $get) {
                                $positionId = $get('position_id');
                                return $positionId == 6 || $positionId == 7 || auth()->user()->faculty_id != null;
                            })
                            ->options(Faculty::pluck(self::getFacultyName(), 'id'))
                            ->default(auth()->user()->faculty_id)
                            ->label('Faculty')
                            ->preload()
                            ->reactive()
                            ->native(false)
                            ->afterStateUpdated(fn(callable $set) => $set('headquarter_id', null)),

                        // Add a hidden field to store the faculty_id value when the select field is disabled
                        Forms\Components\Hidden::make('faculty_id')
                            ->default(auth()->user()->faculty_id)
                            ->visible(fn() => auth()->user()->faculty_id != null),

                        Forms\Components\Select::make('department_id')
                            ->options(function (callable $get) {
                                $faculty = new Faculty();

                                $facultyId = $get('faculty_id');
                                if (!$facultyId) {
                                    return null;
                                }

                                $departments = Department::where('faculty_id', $facultyId)->pluck(self::getFacultyName(), 'id');
                                return $departments;
                            })
                            ->reactive()
                            ->translateLabel()
                            ->native(false)
                            ->label(__('Department'))
                            ->required(function (callable $get) {
                                return $get('role') == Role::where('name', 'Faculty Admin')->first()->id;
                            })
                            ->disabled(fn(Get $get, string $operation): bool => (!filled($get('faculty_id')))),

                        // Add a hidden field to store the department_id value when the select field is disabled
                        Forms\Components\Hidden::make('department_id')
                            ->default(auth()->user()->department_id)
                            ->visible(fn() => auth()->user()->faculty_id != null),

                        Forms\Components\Select::make('headquarter_id')
                            ->options(function (callable $get) {
                                $faculty = new Faculty();

                                $facultyId = $get('faculty_id');
                                if (!$facultyId) {
                                    return null;
                                }

                                $headquartersIds = $faculty->getHeadquarterIds($facultyId)->toArray();
                                if (empty($headquartersIds)) {
                                    return null;
                                }

                                $headquartersNames = Headquarter::whereIn('id', $headquartersIds)->pluck('name')->toArray();

                                $headquarters = array_combine($headquartersIds, $headquartersNames);

                                return $headquarters;
                            })
                            ->reactive()
                            ->translateLabel()
                            ->native(false)
                            ->label(__('Headquarter'))
                            ->required(function (callable $get) {
                                return $get('role') == Role::where('name', 'Faculty Admin')->first()->id;
                            })
                            ->disabled(fn(Get $get, string $operation): bool => (!filled($get('faculty_id')))),

                        // Add a hidden field to store the headquarter_id value when the select field is disabled
                        Forms\Components\Hidden::make('headquarter_id')
                            ->default(auth()->user()->headquarter_id)
                            ->visible(fn() => auth()->user()->faculty_id != null),

                        Forms\Components\Hidden::make('hide_faculty_field')
                            ->default(false)
                            ->reactive(),

                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        $locale = app()->getLocale(); // Get the current application locale
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->label(__('Name'))
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->alignment(Alignment::Center)
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('type')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->type == 'internal') {
                            return __('internal');
                        } else {
                            return 'LDAP';
                        }
                    })
                    ->color(function ($record) {
                        if ($record->type == 'internal') {
                            return 'success';
                        } else {
                            return 'danger';
                        }
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Is Active'))
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->icon(fn(string $state): string => match ($state) {
                        '0' => 'heroicon-o-x-circle',
                        '1' => 'heroicon-o-check-circle',
                        '2' => 'heroicon-o-pause-circle',
                    // })->color(fn(User $record) => $record->is_active ? 'success' : 'danger'),
                    })->color(fn($record) => match ($record->is_active) {
                        0 => 'danger',
                        1 => 'success',
                        2 => 'primary',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make(__('roles'))
                    ->translateLabel()
                    ->relationship(name: 'roles', titleAttribute: self::getPositionAndRoleName()),
                SelectFilter::make(__('position'))->relationship('position', self::getPositionAndRoleName())
                    ->translateLabel(),
                SelectFilter::make('acadimic_rank_id')->relationship('acadimic_rank', self::getPositionAndRoleName())
                    ->label(__('Acadimic rank')),
                SelectFilter::make('is_active')
                    ->label(__('Is Active'))
                    ->options([
                        '1' => __('Active'),
                        '0' => __('Not Active'),
                        '2' => __('Pending')
                    ]),
                SelectFilter::make('faculty')->relationship('faculty', self::getFacultyName())
                    ->translateLabel(),
                SelectFilter::make('department')->relationship('department', self::getFacultyName())
                    ->translateLabel(),
                SelectFilter::make(__('headquarter'))->relationship('headquarter', 'name')
                    ->translateLabel(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->hidden(fn($record) => $record->type == 'ldap'),
            ])
            ->recordUrl(function ($record) {
                return null;
            }) // disable opening edit mode whenr click on row
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->query(function (User $query) {

                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    return $query;
                } else if (auth()->user()->hasRole('Faculty Admin')) {
                    $query = User::where('faculty_id', auth()->user()->faculty_id);
                    return $query;
                } else {
                    $query = User::where('id', auth()->user()->id);
                    return $query;
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'import-ldap' => Pages\ImportLdap::route('/import-ldap'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // This function to handle view button action.
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name')
                    ->translateLabel(),
                TextEntry::make('email')
                    ->translateLabel(),
                TextEntry::make('faculty.' . self::getFacultyName())
                    ->translateLabel(),
                TextEntry::make('department.' . self::getFacultyName())
                    ->translateLabel(),
                TextEntry::make('headquarter.name')
                    ->translateLabel(),
                TextEntry::make('roles.' . self::getPositionAndRoleName())
                    ->label(__('Role')),
                TextEntry::make('position.' . self::getPositionAndRoleName())
                    ->translateLabel(),
                TextEntry::make('acadimic_rank.' . self::getPositionAndRoleName())
                    ->label(__('Acadimic rank')),
                TextEntry::make('type')
                    ->translateLabel()
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->type == 'internal') {
                            return __('internal');
                        } else {
                            return 'LDAP';
                        }
                    })
                    ->color(function ($record) {
                        if ($record->type == 'internal') {
                            return 'success';
                        } else {
                            return 'danger';
                        }
                    }),
                IconEntry::make('is_active')
                    ->label(__('is_active'))
                    ->icon(fn(string $state): string => match ($state) {
                        '0' => 'heroicon-o-x-circle',
                        '1' => 'heroicon-o-check-circle',
                        '2' => 'heroicon-o-pause-circle',
                    // })->color(fn(User $record) => $record->is_active ? 'success' : 'danger'),
                    })->color(fn($record) => match ($record->is_active) {
                        0 => 'danger',
                        1 => 'success',
                        2 => 'primary',
                    }),
                TextEntry::make('updated_at')
                    ->translateLabel()
                    ->dateTime(),
            ])
            ->columns(2)
            ->inlineLabel();
    }

    private static function getPositionAndRoleName()
    {
        return app()->getLocale() == 'en' ? 'name' : 'ar_name';
    }

    public static function getLabel(): ?string
    {
        return __('System User');
    }

    public static function getPluralLabel(): ?string
    {
        return __('System Users');
    }

    private static function getFacultyName()
    {
        return app()->getLocale() == 'en' ? 'en_name' : 'ar_name';
    }

    public static function getGloballySearchableAttributes(): array
    {
        // Only return searchable attributes if the user is a super admin
        // return auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin') ? ['name', 'email'] : [];
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'الأسم' => $record->name,
            'البريد الألكتروني' => $record->email,
        ];
    }

    private static function getLdapUserData($username)
    {
        // Return null if user not found
        if (empty($username)) {
            return null;
        }

        // Instantiate LDAPController
        $ldapController = app(LDAPController::class);
        $ldapData = $ldapController->checkExistUserLdap($username);

        if ($ldapData === 0) {
            return null;
        }

        return [
            'username' => $ldapData['username'],
            'email' => $ldapData['email'],
        ];
    }
}
