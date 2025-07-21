<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    // public function viewAny(User $user): bool
    // {
    //     //
    // }

    // /**
    //  * Determine whether the user can view the model.
    //  */
    // public function view(User $user, Session $session): bool
    // {
    //     //
    // }

    // /**
    //  * Determine whether the user can create models.
    //  */
    // public function create(User $user): bool
    // {
    //     //
    // }

    // /**
    //  * Determine whether the user can update the model.
    //  */
    // public function update(User $user, Session $session): bool
    // {
    //     //
    // }

    // /**
    //  * Determine whether the user can delete the model.
    //  */
    // public function delete(User $user, Session $session): bool
    // {
    //     //
    // }

    // /**
    //  * Determine whether the user can restore the model.
    //  */
    // public function restore(User $user, Session $session): bool
    // {
    //     //
    // }

    // /**
    //  * Determine whether the user can permanently delete the model.
    //  */
    // public function forceDelete(User $user, Session $session): bool
    // {
    //     //
    // }

    public function sessionNumbers(User $user)
    {
        // accept for super, system admin and user with position head of department
        if ($user->hasRole('Super Admin') || $user->hasRole('System Admin')) {
            return true; // Allow these users to accept requests
        }

        return $user->can('session_numbers'); // Adjust permission check as needed
    }

    public function reports(User $user)
    {
        // accept for super, system admin and user with position head of department
        if ($user->hasRole('Super Admin') || $user->hasRole('System Admin')) {
            return true; // Allow these users to accept requests
        }

        return $user->can('reports'); // Adjust permission check as needed
    }
}
