<?php

namespace App\Filament\Auth;

use App\Models\AcadimicRank;
use App\Models\Department;
use App\Models\RegisterRequests;
use App\Models\Faculty;
use App\Models\Headquarter;
use App\Models\Role;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Events\Auth\Registered;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class Register extends BaseRegister
{
    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->callHook('beforeFill');

        $this->form->fill();

        $this->callHook('afterFill');
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(4);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        event(new Registered($user));

        $this->sendEmailVerificationNotification($user);

        // Filament::auth()->login($user);
        Filament::auth()->logout();

        session()->regenerate();

        return app(RegistrationResponse::class);
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

    protected function handleRegistration(array $data): Model
    {
        $data['name'] = $data['ar_name'];
        // dd($data);
        return $this->getUserModel()::create($data);
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([

                        Section::make('User details')
                            ->heading(__('User details'))
                            ->schema([
                                Hidden::make('id')->disabledOn('create'),
                                Hidden::make('is_active')
                                    ->default(2), // let it pending to accept from head of department
                                Hidden::make('position_id')
                                    ->default(1), // let position is acadimic staff

                                // TextInput::make('nickname')
                                //     ->label(__('Nickname'))
                                //     // ->regex('/^[a-zA-Z]+$/')
                                //     ->validationMessages([
                                //         'required' => __('required validation'),
                                //         'regex' => __('regex validation'),
                                //     ])
                                //     ->required(),

                                Select::make('acadimic_rank_id')
                                    ->label(__('Acadimic rank'))
                                    // ->regex('/^[a-zA-Z]+$/')
                                    ->validationMessages([
                                        'required' => __('required validation'),
                                        'regex' => __('regex validation'),
                                    ])
                                    ->options(AcadimicRank::pluck(self::getAcadimicRankName(), 'id'))
                                    ->required(),

                                Hidden::make('name'),
                                // ->required()
                                // ->hidden()
                                // ->translateLabel()
                                // ->validationMessages([
                                //     'required' => __('required validation'),
                                // ])
                                // ->maxLength(255),

                                TextInput::make('ar_name')
                                    ->label(__('Arabic name in three parts'))
                                    ->required()
                                    ->rules(['regex:/^([\p{Arabic}\s]+ ){2}[\p{Arabic}\s]+$/u'])
                                    ->validationMessages([
                                        'regex' => __('regex validation'),
                                        'required' => __('required validation'),
                                    ])
                                    ->reactive()
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

                                    /* Here user can create the same faculty admin with the same phone on a different college not the same one */
                                    // ->rules(function (callable $get) {
                                    //     $role = $get('role');
                                    //     $facultyId = $get('faculty_id');
                                    //     $id = $get('id'); // Get the current record ID

                                    //     $rules = ['required', 'regex:/^9665[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/', 'digits:12'];

                                    //     // Check if the role is Faculty Admin
                                    //     if ($role == Role::where('name', 'Faculty Admin')->first()->id) {
                                    //         // Apply unique constraint on phone + faculty_id combination, ignoring the current record
                                    //         $rules[] = Rule::unique('users', 'phone')->ignore($id)->where('faculty_id', $facultyId);
                                    //     } else {
                                    //         // Apply regular unique constraint on phone, ignoring the current record
                                    //         $rules[] = Rule::unique('users', 'phone')->ignore($id);
                                    //     }

                                    //     return $rules;
                                    // })
                                    ->validationMessages([
                                        'regex' => __('regex validation'),
                                        'min' => __('min validation') . '12',
                                        'max' => __('max validation') . '12',
                                        'required' => __('required validation'),
                                    ])
                                    ->minLength(12)
                                    ->maxLength(12),

                                // TextInput::make('username')
                                //     ->label(__('Username'))
                                //     ->regex('/^(?=.*[a-zA-Z])[a-zA-Z0-9]+$/')
                                //     ->required()
                                //     ->validationMessages([
                                //         'regex' => __('regex validation'),
                                //         'required' => __('required validation'),
                                //     ]),

                                TextInput::make('email')
                                    ->email()
                                    ->unique(User::class, 'email', ignoreRecord: true) // Specify the User model and the email column
                                    /* Here user can create the same faculty admin with the same email on a different college not the same one */
                                    // ->rules(function (callable $get) {
                                    //     $role = $get('role');
                                    //     $facultyId = $get('faculty_id');
                                    //     $id = $get('id'); // Get the current record ID

                                    //     // $rules = ['required', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9]+$/'];

                                    //     // Check if the role is Faculty Admin
                                    //     if ($role == Role::where('name', 'Faculty Admin')->first()->id) {
                                    //         // Apply unique constraint on email + faculty_id combination, ignoring the current record
                                    //         $rules[] = Rule::unique('users', 'email')->ignore($id)->where('faculty_id', $facultyId);
                                    //     } else {
                                    //         // Apply regular unique constraint on email, ignoring the current record
                                    //         $rules[] = Rule::unique('users', 'email')->ignore($id);
                                    //     }

                                    //     return $rules;
                                    // })
                                    ->validationMessages([
                                        'regex' => __('regex validation'),
                                        'email' => __('email validation'),
                                        'unique' => __('unique validation'),
                                        'required' => __('required validation'),
                                    ])
                                    ->translateLabel()
                                    ->required()
                                    ->maxLength(255),

                                Select::make('faculty_id')->label(__('Faculty'))
                                    ->options(Faculty::all()->pluck(self::getFacultyOrDepartmentName(), 'id'))
                                    ->reactive()
                                    ->native(false)
                                    ->required()
                                    ->validationMessages([
                                        'required' => __('required validation'),
                                    ])
                                    ->afterStateUpdated(fn(callable $set) => $set('headquarter_id', null) & $set('department_id', null)),

                                Select::make('department_id')->label(__('Department'))
                                    // ->options(Department::where('faculty_id')->pluck(self::getFacultyOrDepartmentName(), 'id'))
                                    ->options(function (callable $get) {
                                        $facultyId = $get('faculty_id') ?? [];
                                        $departments = Department::where('faculty_id', $facultyId)->pluck(self::getFacultyOrDepartmentName(), 'id');
                                        return $departments;
                                    })
                                    ->reactive()
                                    ->native(false)
                                    ->required()
                                    ->validationMessages([
                                        'required' => __('required validation'),
                                    ])
                                    ->disabled(fn(Get $get): bool => (!filled($get('faculty_id')))), // enable when choose faculty

                                Select::make('headquarter_id')->label(__('Headquarter'))
                                    ->options(function (callable $get, Faculty $faculty) {
                                        $facultyId = $get('faculty_id') ?? [];
                                        $headquartersIds = $faculty->getHeadquarterIds($facultyId)->toArray();
                                        $headquartersNames = Headquarter::whereIn('id', $headquartersIds)->pluck('name')->toArray();
                                        $headquarters = array_combine($headquartersIds, $headquartersNames);

                                        return $headquarters;
                                    })
                                    ->reactive()
                                    ->native(false)
                                    ->required()
                                    ->validationMessages([
                                        'required' => __('required validation'),
                                    ])
                                    ->disabled(fn(Get $get): bool => (!filled($get('faculty_id')))), // enable when choose faculty

                                // TextInput::make('password')
                                //     ->password()
                                //     ->revealable()
                                //     ->translateLabel()
                                //     ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                                //     ->dehydrated(fn(?string $state): bool => filled($state))
                                //     // ->required(fn(string $operation): bool => $operation === 'create')
                                //     ->required()
                                //     ->maxLength(255)
                                //     ->minLength(8)
                                //     ->validationMessages([
                                //         'regex' => __('regex validation'),
                                //         'required' => __('required validation'),
                                //         'max' => __('max validation') . '255',
                                //         'min' => __('min validation') . '8',
                                //     ]),

                                $this->getPasswordFormComponent(),
                                $this->getPasswordConfirmationFormComponent(),

                                FileUpload::make('signature')
                                    ->label(__('Signature'))
                                    ->required()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif']) // Specify accepted file types
                                    ->maxSize(2 * 1024) // Max size in kilobytes
                                    ->helperText(__('Upload your signature image. Max size: 2MB. Accepted formats: JPEG, PNG, GIF.'))
                                    ->preserveFilenames() // Optionally preserve original filenames
                                    ->disk('public') // Specify the disk where the file will be stored
                                    ->directory('signatures') // Specify the directory within the disk
                                    ->downloadable(), // Make the file downloadable
                            ]),
                    ])
                    ->statePath('data'),
            ),
        ];
    }
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/register.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            // ->rule(Password::default())
            ->dehydrateStateUsing(fn($state) => Hash::make($state))
            ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute'))
            ->required()
            ->maxLength(255)
            ->minLength(8)
            ->extraAttributes(['oncopy' => 'return false;', 'onpaste' => 'return false;']) // Prevent copy-paste
            ->validationMessages([
                'regex' => __('regex validation'),
                'same' => __('same validation'),
                'required' => __('required validation'),
                'max' => __('max validation') . '255',
                'min' => __('min validation') . '8',
            ]);
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->same('password')
            ->dehydrated(false)
            ->extraAttributes(['oncopy' => 'return false;', 'onpaste' => 'return false;']) // Prevent copy-paste
            ->validationMessages([
                'regex' => __('regex validation'),
                'same' => __('same validation') . __('Password'),
                'required' => __('required validation'),
            ]);
    }

    protected function getUserModel(): string
    {
        if (isset($this->userModel)) {
            return $this->userModel;
        }

        $authGuard = Filament::auth();

        $provider = $authGuard->getProvider();

        return $this->userModel = $provider->getModel();
    }

    protected function afterRegister(): void
    {
        $userRole = Role::where('name', 'Member')->first();
        $user = $this->form->getRecord();
        $userId = $this->form->getRecord()->id;
        $departmentId = $this->form->getState()['department_id'];

        $user->assignRole($userRole);  // Assign role to the created user

        $registerRequest = new RegisterRequests();
        $registerRequest->create([
            'user_id' => $userId,
            'department_id' => $departmentId,
        ]);

        Notification::make()
            ->title(__('Your account is pending to approve'))
            ->icon('heroicon-o-pause-circle')
            ->info()
            ->color('info')
            // ->duration(2500)
            ->send();
    }
    private static function getFacultyOrDepartmentName()
    {
        return app()->getLocale() == 'en' ? 'en_name' : 'ar_name';
    }
    private static function getAcadimicRankName()
    {
        return app()->getLocale() == 'en' ? 'name' : 'ar_name';
    }
    public function getHeading(): string | Htmlable
    {
        return __('filament-panels::pages/auth/register.heading') . " " . __('at') . " " . __('Council minutes system');
    }
}
