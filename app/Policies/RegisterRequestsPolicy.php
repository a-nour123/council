<?php

namespace App\Policies;

use App\Models\RegisterRequests;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RegisterRequestsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if( $user->position_id == 3 || $user->hasRole('Super Admin') || $user->hasRole('System Admin') || $user->hasRole('Faculty Admin') ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RegisterRequests $registerRequests)
    {
        if( $user->position_id == 3 || $user->hasRole('Super Admin') || $user->hasRole('System Admin') || $user->hasRole('Faculty Admin') ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RegisterRequests $registerRequests)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RegisterRequests $registerRequests)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RegisterRequests $registerRequests)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RegisterRequests $registerRequests)
    {
        //
    }
}
