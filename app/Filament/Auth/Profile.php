<?php

// namespace Filament\Pages\Auth;
namespace App\Filament\Auth;

use App\Models\AcadimicRank;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Headquarter;
use App\Models\User;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\Alignment;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Js;
use Illuminate\Validation\Rules\Password;
use Throwable;

use function Filament\Support\is_app_url;

/**
 * @property Form $form
 */
class Profile extends Page
{
    use Concerns\CanUseDatabaseTransactions;
    use Concerns\HasMaxWidth;
    use Concerns\HasTopbar;
    use Concerns\InteractsWithFormActions;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected static bool $isDiscovered = false;

    public function getLayout(): string
    {
        return static::$layout ?? (static::isSimple() ? 'filament-panels::components.layout.simple' : 'filament-panels::components.layout.index');
    }

    public static function isSimple(): bool
    {
        return Filament::isProfilePageSimple();
    }

    public function getView(): string
    {
        return static::$view ?? 'filament-panels::pages.auth.edit-profile';
    }

    public static function getLabel(): string
    {
        return __('filament-panels::pages/auth/edit-profile.label');
    }

    public static function getRelativeRouteName(): string
    {
        return 'profile';
    }

    public static function isTenantSubscriptionRequired(Panel $panel): bool
    {
        return false;
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    public function getUser(): Authenticatable & Model
    {
        $user = Filament::auth()->user();

        if (! $user instanceof Model) {
            throw new Exception('The authenticated user object must be an Eloquent model to allow the profile page to update it.');
        }

        return $user;
    }

    protected function fillForm(): void
    {
        $data = $this->getUser()->attributesToArray();

        $this->callHook('beforeFill');

        $data = $this->mutateFormDataBeforeFill($data);

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    public static function registerRoutes(Panel $panel): void
    {
        if (filled(static::getCluster())) {
            Route::name(static::prependClusterRouteBaseName(''))
                ->prefix(static::prependClusterSlug(''))
                ->group(fn() => static::routes($panel));

            return;
        }

        static::routes($panel);
    }

    public static function getRouteName(?string $panel = null): string
    {
        $panel = $panel ? Filament::getPanel($panel) : Filament::getCurrentPanel();

        return $panel->generateRouteName('auth.' . static::getRelativeRouteName());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // dd($data);
        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $this->handleRecordUpdate($this->getUser(), $data);

            $this->callHook('afterSave');

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        if (request()->hasSession() && array_key_exists('password', $data)) {
            request()->session()->put([
                'password_hash_' . Filament::getAuthGuard() => $data['password'],
            ]);
        }

        $this->data['password'] = null;
        $this->data['passwordConfirmation'] = null;

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        return $record;
    }

    protected function getSavedNotification(): ?Notification
    {
        $title = $this->getSavedNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($this->getSavedNotificationTitle());
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('filament-panels::pages/auth/edit-profile.notifications.saved.title');
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/edit-profile.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->autocomplete('new-password')
            ->dehydrated(fn($state): bool => filled($state))
            ->dehydrateStateUsing(fn($state): string => Hash::make($state))
            ->live(debounce: 500)
            ->extraAttributes(['oncopy' => 'return false;', 'onpaste' => 'return false;']) // Prevent copy-paste
            ->same('passwordConfirmation');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::pages/auth/edit-profile.form.password_confirmation.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->visible(fn(Get $get): bool => filled($get('password')))
            ->extraAttributes(['oncopy' => 'return false;', 'onpaste' => 'return false;']) // Prevent copy-paste
            ->dehydrated(false);
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Select::make('acadimic_rank_id')
                            ->label(__('Acadimic rank'))
                            ->validationMessages([
                                'required' => __('required validation'),
                                'regex' => __('regex validation'),
                            ])
                            ->options(AcadimicRank::pluck(self::getPositionAndRoleName(), 'id')),
                            // ->required(),

                        TextInput::make('name')
                            ->required()
                            ->hidden()
                            ->translateLabel()
                            ->validationMessages([
                                'required' => __('required validation'),
                            ])
                            ->maxLength(255),

                        TextInput::make('ar_name')
                            ->label(__('Arabic name in three parts'))
                            // ->required()
                            ->rules(['regex:/^([\p{Arabic}\s]+ ){2}[\p{Arabic}\s]+$/u'])
                            ->validationMessages([
                                'regex' => __('regex validation'),
                                'required' => __('required validation'),
                            ])
                            ->placeholder(__('Please enter the arabic name in three parts')),

                        TextInput::make('en_name')
                            ->label(__('ُEnglish name in three parts'))
                            // ->required()
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
                            // ->required()
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

                        TextInput::make('username')
                            ->readOnly(fn($record) => $record->type === 'ldap')
                            ->label(__('Username'))
                            ->regex('/^(?=.*[a-zA-Z])[a-zA-Z0-9]+$/')
                            // ->required()
                            ->validationMessages([
                                'regex' => __('regex validation'),
                                'required' => __('required validation'),
                            ]),

                        TextInput::make('email')
                            ->readOnly(fn($record) => $record->type === 'ldap')
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
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data')
                    ->inlineLabel(! static::isSimple()),
            ),
        ];
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return $this->backAction();
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('filament-panels::pages/auth/edit-profile.form.actions.save.label'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::Start;
    }

    public function getTitle(): string | Htmlable
    {
        return static::getLabel();
    }

    public static function getSlug(): string
    {
        return static::$slug ?? 'profile';
    }

    public function hasLogo(): bool
    {
        return false;
    }

    /**
     * @deprecated Use `getCancelFormAction()` instead.
     */
    public function backAction(): Action
    {
        return Action::make('back')
            ->label(__('filament-panels::pages/auth/edit-profile.actions.cancel.label'))
            ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = ' . Js::from(filament()->getUrl()) . ')')
            ->color('gray');
    }

    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => $this->hasTopbar(),
            'maxWidth' => $this->getMaxWidth(),
        ];
    }

    private static function getFacultyOrDepartmentName()
    {
        return app()->getLocale() == 'en' ? 'en_name' : 'ar_name';
    }

    private static function getPositionAndRoleName()
    {
        return app()->getLocale() == 'en' ? 'name' : 'ar_name';
    }
}
