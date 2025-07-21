<?php

namespace App\Policies;

use App\Models\User;

use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_user');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->can('view_user');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('Super Admin') || $user->hasRole('System Admin') || $user->hasRole('Faculty Admin')) {
            return $user->can('create_user');
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    // public function update(User $user, User $targetUser)
    // {
    //     if ($user->hasRole('System Admin')) {
    //         return $targetUser->hasAnyRole(['User', 'Faculty Admin']);
    //     } elseif ($user->hasRole('Faculty Admin') && $user->faculty_id == $targetUser->faculty_id) {
    //         return $targetUser->hasRole('User');

    //         // Allow the user to edit their own profile
    //     } elseif ($user->id === $targetUser->id) {
    //         return true;
    //     }
    //     return false;
    // }

    public function update(User $user, User $targetUser): bool
    {
        // Check if the user has a privileged role
        if ($user->hasRole('Super Admin') || $user->hasRole('System Admin')) {
            return $user->can('update_user');
        }

        // Allow users to update their own profile
        if ($user->id === $targetUser->id) {
            return $user->can('update_user');
        }

        // Deny access by default
        return false;
    }


    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->can('delete_user');
    }

    /**
     * Determine whether the user can bulk delete.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_user');
    }

    /**
     * Determine whether the user can permanently delete.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function forceDelete(User $user): bool
    {
        return $user->can('force_delete_user');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_user');
    }

    /**
     * Determine whether the user can restore.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function restore(User $user): bool
    {
        return $user->can('restore_user');
    }

    /**
     * Determine whether the user can bulk restore.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_user');
    }

    /**
     * Determine whether the user can bulk restore.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function replicate(User $user): bool
    {
        return $user->can('replicate_user');
    }

    /**
     * Determine whether the user can reorder.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_user');
    }

    /**
     * Deactivate a user based on the current user's role and the target user's role.
     *
     * @param User $user       The current user
     * @param User $targetUser The user to deactivate
     *
     * @return bool Whether the target user can be deactivated
     */
    public function deactivateUser(User $user, User $targetUser)
    {
        if ($user->hasRole('System Admin')) {
            return $targetUser->hasAnyRole(['User', 'Faculty Admin']);
        } elseif ($user->hasRole('Faculty Admin')) {
            return $targetUser->hasRole('User');
        }
        return false;
    }

    public function ldapConfigration(User $user)
    {
        // accept for super, system admin and user with position head of department
        if ($user->hasRole('Super Admin') || $user->hasRole('System Admin')) {
            return true; // Allow these users to handle the ldap configuration
        }

        return $user->can('ldapConfigration'); // Adjust permission check as needed
    }
}
