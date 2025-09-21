<?php

namespace App\Filament\Resources\LDAPResource\Pages;

use App\Filament\Resources\LDAPResource;
use Filament\Actions;
use App\Models\{
    LDAP
};
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ManageLDAPS extends Page
{
    protected static string $resource = LDAPResource::class;
    protected static string $view = 'filament.resources.ldap.ldap';
    public $ldapSettings;

    public function mount()
    {
        $this->ldapSettings = LDAP::first();
    }

    public function getTitle(): string|Htmlable
    {
        return __('LDAP Settings');
    }

    public function getBreadcrumb(): ?string
    {
        return null;
    }
}

