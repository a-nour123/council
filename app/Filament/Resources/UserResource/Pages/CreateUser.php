<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\ImportAction;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /*
   getRedirectUrl function it to redirect the user to list page after creation
   */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(UserImporter::class)
                ->label(__('Import Users'))
                ->color('success'),

            Action::make('import-ldap')
                ->label(__('Import users from LDAP'))
                ->color('info')
                ->action(action: function ($record) {
                    $appURL = env('APP_URL');

                    // Build the URL dynamically
                    $url = $appURL . '/admin/users/import-ldap';

                    return redirect()->away($url);
                }),
        ];
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('User Created Successfully');
    }

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     dd($data);
    // }

    /*
    This function ensures that when a new user is created, it is automatically assigned a role based on the role ID provided in the form data.
    */
    protected function afterCreate(): void
    {
        $user = $this->record;
        $data = $this->data;
        // $user = new User;
        $user->name = $data['ar_name'];
        $user->save();

        // Extract role ID from the submitted data
        $roleID = $data['role'];

        // dd($roleID);

        // Extract role ID from the submitted data
        $role = Role::findById($roleID);

        // Extract role name
        $roleName = $role->name;

        $user->assignRole($roleName);
    }
}
