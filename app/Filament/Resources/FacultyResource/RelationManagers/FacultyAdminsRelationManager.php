<?php

namespace App\Filament\Resources\FacultyResource\RelationManagers;

use App\Models\Faculty;
use App\Models\Headquarter;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class FacultyAdminsRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    // The title of the relation manager
    protected static ?string $title = 'المستخدمين';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('id'),

                TextInput::make('nickname')
                    ->label(__('Nickname'))
                    // ->regex('/^[a-zA-Z]+$/')
                    ->required()
                    ->validationMessages([
                        'regex' => __('regex validation'),
                        'required' => __('required validation'),
                    ]),

                TextInput::make('ar_name')
                    ->label(__('Arabic name in three parts '))
                    ->required()
                    // ->rules(['regex:/^([\p{Arabic}\s]+ ){3}[\p{Arabic}\s]+$/u'])
                    ->rules(['regex:/^([\p{Arabic}\s]+ ){2}[\p{Arabic}\s]+$/u'])
                    // ->regex('#^(\w+)\s+(\w+)\s+(\w+)\s+(\w+)$#')
                    ->validationMessages([
                        'regex' => __('regex validation'),
                        'required' => __('required validation'),
                    ])
                    ->placeholder(__('Please enter the arabic name in three parts')),

                TextInput::make('en_name')
                    ->label(__('ُEnglish name in three parts'))
                    ->required()
                    // ->regex('#^(\w+)\s+(\w+)\s+(\w+)\s+(\w+)$#')
                    ->regex('/^\w+ \w+ \w+$/')
                    ->placeholder(__('Please enter the name in three parts'))
                    ->validationMessages([
                        'regex' => __('regex validation'),
                        'required' => __('required validation'),
                    ]),

                TextInput::make('phone')
                    ->label(__('Phone number'))
                    ->tel()
                    ->placeholder(__('Please enter a number starts with 9665 and with 12 digits.'))
                    ->telRegex('/^9665[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                    ->required()
                    ->unique(User::class, 'phone', ignoreRecord: true) // Specify the User model and the phone column
                    ->minLength(12)
                    ->maxLength(12)
                    ->validationMessages([
                        'regex' => __('regex validation'),
                        'required' => __('required validation'),
                    ]),

                TextInput::make('username')
                    ->label(__('Username'))
                    ->required()
                    ->validationMessages([
                        'required' => __('required validation'),
                    ]),

                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->unique(User::class, 'email', ignoreRecord: true) // Specify the User model and the email column
                    ->required()
                    ->validationMessages([
                        // 'regex' => __('regex validation'),
                        'email' => __('email validation'),
                        'unique' => __('unique validation'),
                        'required' => __('required validation'),
                    ]),

                TextInput::make('password')
                    ->password()
                    ->translateLabel()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->validationMessages([
                        'required' => __('required validation'),
                        'min' => __('min validation') . '8',
                    ]),

                TextInput::make('confirm_password')
                    ->label('Confirm password')
                    ->label(__('Confirm password'))
                    ->same('password')
                    ->minLength(8)
                    ->required()
                    ->validationMessages([
                        'same' => __('same validation') . __('Password'),
                        // 'min' => __('min validation') . '8',
                    ]),

                Forms\Components\Section::make('Related details')
                    ->heading(__('Related details'))
                    ->schema([

                        Forms\Components\Select::make('role')
                            // ->default(function (callable $get) {
                            //     return Role::where('name', 'Faculty Admin')->first()->id;
                            // })
                            // ->multiple()
                            ->translateLabel()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->options(function (callable $get) { // display all roles as options except super admin
                                return Role::where('name', ['Faculty Admin'])->pluck(self::getPositionAndRoleName(), 'id');
                            })
                            ->validationMessages([
                                'required' => __('required validation'),
                            ]),

                    ])->columns(3)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading(__('No admins yet')) // empty data message
            ->emptyStateDescription('') // empty data message description
            ->heading(__('Admins'))
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('name')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable()
                    ->translateLabel(),

                Tables\Columns\TextColumn::make('email')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable()
                    ->translateLabel(),

                Tables\Columns\IconColumn::make('is_active')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->label(__('Is Active'))
                    ->translateLabel()
                    ->icon(fn(string $state): string => match ($state) {
                        '0' => 'heroicon-o-x-circle',
                        '1' => 'heroicon-o-check-circle',
                    })
                    ->color(fn(User $record) => $record->is_active ? 'success' : 'danger'),

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
                // filteration by users activation
                SelectFilter::make('is_active')
                    ->label(__('Is Active'))
                    ->options([
                        '1' => __('Active'),
                        '0' => __('Not Active')
                    ])
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Add Admin'))
                    ->modalHeading(__("Add Admin"))
                    ->slideOver()
                    ->action(function (array $data): void {

                        // Prepare data array for insertion
                        $insertData = [];

                        $faculty_id = $this->getOwnerRecord()->id;
                        $headquarter_id = $this->getOwnerRecord()->headquarter_id;

                        $insertData = [
                            'nickname' => $data['nickname'],
                            'ar_name' => $data['ar_name'],
                            'en_name' => $data['en_name'],
                            'name' => $data['ar_name'],
                            'phone' => $data['phone'],
                            'username' => $data['username'],
                            'email' => $data['email'],
                            'faculty_id' => $faculty_id, // Associate the faculty record with the user
                            'headquarter_id' => $headquarter_id, // Associate the faculty record with the user
                            'password' => bcrypt($data['password']), // Hash the password before saving
                        ];

                        // Insert data into the database and retrieve the user instance
                        $user = User::create($insertData);

                        // Assign the role to the user
                        $roleID = $data['role'];
                        $role = Role::find($roleID);

                        if ($role) {
                            $user->assignRole($role->name);
                        }

                        // Display success notification
                        Notification::make()
                            ->title(__('Admin added successfully'))
                            ->success()
                            ->color('success')
                            ->send();
                    })
                    ->hidden(fn(): bool => (auth()->user()->hasRole('Faculty Admin'))),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver()->modalHeading(__("Edit Admin"))
                    ->action(function (array $data): void {
                        $userId = $data['id'];

                        // Prepare data array for insertion
                        $insertData = [];

                        $faculty_id = $this->getOwnerRecord()->id;
                        $headquarter_id = $this->getOwnerRecord()->headquarter_id;

                        $insertData = [
                            'nickname' => $data['nickname'],
                            'ar_name' => $data['ar_name'],
                            'en_name' => $data['en_name'],
                            'name' => $data['ar_name'],
                            'phone' => $data['phone'],
                            'username' => $data['username'],
                            'email' => $data['email'],
                            'faculty_id' => $faculty_id, // Associate the faculty record with the user
                            'headquarter_id' => $headquarter_id, // Associate the faculty record with the user
                            'password' => bcrypt($data['password']), // Hash the password before saving
                        ];

                        $user = User::find($userId);
                        $user->update($insertData);

                        // Assign the role to the user
                        $roleID = $data['role'];
                        $role = Role::find($roleID);

                        if ($role) {
                            $user->assignRole($role->name);
                        }

                        // Display success notification
                        Notification::make()
                            ->title(__('Admin added successfully'))
                            ->success()
                            ->color('success')
                            ->send();
                    })
                    ->hidden(fn(): bool => (auth()->user()->hasRole('Faculty Admin'))),
                Tables\Actions\DeleteAction::make()->modalHeading(__("Delete Admin")),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->modalHeading(__("Delete Admins")),
                ]),
            ]);
    }
    private static function getFacultyName()
    {
        return app()->getLocale() == 'en' ? 'en_name' : 'ar_name';
    }
    private static function getPositionAndRoleName()
    {
        return app()->getLocale() == 'en' ? 'name' : 'ar_name';
    }
}
