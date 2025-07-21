<?php

namespace App\Policies;

use App\Models\ControlReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ControlReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if( in_array($user->position_id, [2,3,4,5]) || $user->hasRole('Super Admin') || $user->hasRole('System Admin') ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ControlReport $controlReport)
    {
        //
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
    public function update(User $user, ControlReport $controlReport)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ControlReport $controlReport)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ControlReport $controlReport)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ControlReport $controlReport)
    {
        //
    }
}
