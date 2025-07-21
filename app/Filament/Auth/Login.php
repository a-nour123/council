<?php
namespace App\Filament\Auth;

use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Pages\Auth\Login as BaseAuth;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use App\Models\LDAP;
use App\Models\User;
use LdapRecord\Auth\BindException;
use LdapRecord\Connection;
use LdapRecord\Container;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class Login extends BaseAuth
{
    protected $connection, $container;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // $this->getEmailFormComponent(),
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->label(__('Email or username'))
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login_type = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $password = $data['password'];

        $checkUser = User::where($login_type, $data['login'])->first();

        // Handle LDAP users
        if ($checkUser && $checkUser->type === 'ldap') {
            try {
                if ($this->LdapConnection()) {

                    if ($this->connection) {
                        $user = $this->connection->query()->where('samaccountname', '=', $checkUser->username)->first();

                        if ($user) {
                            $dn = $user['distinguishedname'][0];
                            $components = explode(",", $dn);
                            $firstDcValue = null;

                            foreach ($components as $component) {
                                if (strpos($component, "DC=") === 0) {
                                    $firstDcValue = substr($component, 3);
                                    break;
                                }
                            }

                            $authUser = $this->connection->auth()->attempt(
                                $firstDcValue . '\\' . $checkUser->username,
                                $password
                            );

                            if ($authUser) {
                                // Update local password hash
                                $checkUser->update(['password' => Hash::make($password)]);
                            }
                        } else {
                            $this->throwFailureValidationException();
                        }
                    }
                }
                // Return credentials for Filament auth
                return [
                    $login_type => $data['login'],
                    'password' => $password
                ];

            } catch (\Exception $e) {

                $this->throwFailureValidationException();
            }
        }

        // Default local auth
        return [
            $login_type => $data['login'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('Email or password is incorrect'),
        ]);
    }

    public function getHeading(): string|Htmlable
    {
        return __('filament-panels::pages/auth/login.title') . " " . __('at') . " " . __('Council minutes system');
    }

    protected function LdapConnection()
    {
        $ldapSettings = LDAP::first();

        // Split the DN string by commas
        $base_dn = explode(",", $ldapSettings->base_dn);
        $firstDcValue = null;
        foreach ($base_dn as $component) {
            if (strpos($component, "DC=") === 0) {
                // Extract the value of the first "DC" component
                $firstDcValue = substr($component, 3);
                break;
            }
        }

        $connection = new Connection([
            'hosts' => explode(',', $ldapSettings->hosts),
            'port' => $ldapSettings->port,
            'base_dn' => $ldapSettings->base_dn,
            'username' => $firstDcValue . '\\' . $ldapSettings->username,
            'password' => Crypt::decrypt($ldapSettings->password),
            // Optional Configuration Options
            'use_ssl' => ($ldapSettings->ssl == '1') ? true : false,
            'use_tls' => ($ldapSettings->tls == '1') ? true : false,
            'version' => (int) $ldapSettings->version,
            'timeout' => (int) $ldapSettings->timeout,
            'follow_referrals' => ($ldapSettings->follow == '1') ? true : false,
        ]);

        try {
            $connection->connect();
            $container = Container::addConnection($connection);
            $this->connection = $connection;
            $this->container = $container;
        } catch (BindException $e) {
            return false;
        }

    }

}
