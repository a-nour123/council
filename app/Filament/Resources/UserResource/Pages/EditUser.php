<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Department;
use App\Models\Department_Council;
use App\Models\Faculty;
use App\Models\FacultyCouncil;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Forms;
use Illuminate\Support\Facades\Gate;


class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function mount($record): void
    {
        $user = User::findOrFail($record);
        if ($user->type == 'ldap') {
            abort(403, __('You cannot edit LDAP users'));
        }
        parent::mount($record);
    }

    public function getActions(): array
    {
        return [
            Action::make('deactivate') // Action to deactivate/activate the user
                ->label(fn(User $record) => $record->is_active ? 'Deactivate User' : 'Activate User')
                ->requiresConfirmation() // Display a confirmation dialog before action
                ->color(fn(User $record) => $record->is_active ? 'danger' : 'success')
                ->hidden(!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('System Admin'))
                ->visible(function (User $record) {
                    // Get the currently logged-in user
                    $loggedInUser = auth()->user();

                    // Check if the logged-in user has permission to deactivate the user
                    // and also ensure they are not deactivating themselves
                    return Gate::allows('deactivateUser', [$record, User::class]) && $record->id !== $loggedInUser->id;
                }) // Control action visibility based on user's permissions
                ->action(function (User $record) { // Action callback to update user's active status
                    $record->update($record->is_active ? ['is_active' => 0] : ['is_active' => 1]);

                    // Flash a success message after deactivation
                    Notification::make()
                                ->title('Status Changed Successfully')
                                ->icon('heroicon-o-check-circle')
                        ->{$record->is_active ? 'success' : 'danger'}()
                            ->send();
                })
                ->modalHeading(fn(User $record) => $record->is_active ? 'Deactivate User' : 'Activate User')
                ->modalDescription(fn(User $record) => $record->is_active ? 'Are you sure you want to deactivate this user? This will prevent them from logging in.' : 'Are you sure you want to activate this user? This will allow them to log in.'),

        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('User data has been updated');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['name'] = $data['ar_name'];
        $userId = $data['id'];
        $userFacultyId = $data['faculty_id'];

        $facultyDepartments = Department::where('faculty_id', $userFacultyId)->pluck('id')->toArray();

        $userDepartmentIdFromCouncil = Department_Council::where('user_id', $userId)->pluck('department_id')->toArray();

        $userFacultyIdFromCouncil = FacultyCouncil::where('user_id', $userId)->value('faculty_id');
        // $council = Department_Council::where('user_id',$userId)->where('department_id',$userDepartmentIdFromCouncil)->value('id');

        // if (!in_array($userDepartmentIdFromCouncil, $facultyDepartments)) {
        //     $records = Department_Council::where('user_id', $userId)->whereIn('department_id', $userDepartmentIdFromCouncil)->get();

        //     foreach ($records as $record) {
        //         $record->delete();
        //     }
        // }

        if ($userFacultyId != $userFacultyIdFromCouncil) {
            FacultyCouncil::where('user_id', $userId)->where('faculty_id', $userFacultyIdFromCouncil)->delete();
        }

        return $data;
    }

}
