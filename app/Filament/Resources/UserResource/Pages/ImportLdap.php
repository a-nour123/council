<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Http\Controllers\LDAPController;
use Filament\Actions;
use App\Models\{
    LDAP,
    User
};
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ImportLdap extends Page
{
    protected static string $resource = UserResource::class;
    protected static string $view = 'filament.resources.ldap.import';
    public $ldapSettings, $ldapUsers;

    public function mount()
    {
        $this->ldapSettings = LDAP::first();

        $ldapController = app(LDAPController::class);
        $usersFromLDAP = $ldapController->getLdapUsers();
        $currentLDAPUsers = User::where('type', 'ldap')
            ->select('email', 'username')
            ->get()
            ->toArray();

        // Convert currentLDAPUsers to a lookup array for fast checking
        $currentLDAPLookup = collect($currentLDAPUsers)->pluck('email', 'username')->toArray();

        // Map over usersFromLDAP and add the "exist" key
        $this->ldapUsers = array_map(function ($user) use ($currentLDAPLookup) {
            $user['exist'] = isset($currentLDAPLookup[$user['username']]) || in_array($user['email'], $currentLDAPLookup);
            return $user;
        }, $usersFromLDAP);
    }

    public function getTitle(): string|Htmlable
    {
        return __('Import users from LDAP');
    }

    public function getBreadcrumb(): ?string
    {
        return null;
    }
}

