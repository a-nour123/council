<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Headquarter;
use Illuminate\Auth\Access\HandlesAuthorization;

class HeadquarterPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_headquarter');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Headquarter $headquarter): bool
    {
        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin' )) {
            return $user->can('view_headquarter');
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin' )) {
            return $user->can('create_headquarter');
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Headquarter $headquarter): bool
    {
        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin' )) {
            return $user->can('update_headquarter');
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Headquarter $headquarter): bool
    {
        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin' )) {
            return $user->can('delete_headquarter');
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin' )) {
            return $user->can('delete_any_headquarter');
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Headquarter $headquarter): bool
    {
        return $user->can('force_delete_headquarter');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_headquarter');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Headquarter $headquarter): bool
    {
        return $user->can('restore_headquarter');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_headquarter');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Headquarter $headquarter): bool
    {
        return $user->can('replicate_headquarter');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_headquarter');
    }
}
