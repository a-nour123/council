<?php

namespace App\Policies;

use App\Models\FacultySession;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FacultySessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FacultySession $facultySession)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FacultySession $facultySession)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FacultySession $facultySession)
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FacultySession $facultySession)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FacultySession $facultySession)
    {
        //
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
