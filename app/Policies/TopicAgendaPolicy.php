<?php

namespace App\Policies;

use App\Models\TopicAgenda;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\DB;

class TopicAgendaPolicy
{
    public function viewAny(User $user)
    {
        return $user->can('view_any_submit::topic');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TopicAgenda $agenda)
    {
        // $depId = DB::table('department__councils')
        //     ->where('user_id', $user->id)
        //     ->get()->first();

        $depId = auth()->user()->department_id;

        if (is_null($depId)) {
            // Handle the case where no department council record is found
            return false;
        }
        if ($user->name === 'Super Admin' || $user->name === 'System Admin') {
            return $user->can('view_submit::topic') || $user->faculty_id === $agenda->faculty_id;
        } elseif ($agenda->department_id === $depId) {
            return $user->can('view_submit::topic') || $agenda->department_id === $depId;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        // $depId = DB::table('department__councils')
        //     ->where('user_id', $user->id)
        //     ->get()->first();

        // if (is_null($depId)) {
        //     // Handle the case where no department council record is found
        //     return false;
        // }
        // // dump($depId->department_id);
        // // if (in_array($user->position_id, ['1', '2', '5']) && $depId->department_id) {
        // if (in_array($user->position_id, ['1', '2', '3']) && $depId->department_id) {
        return $user->can('create_submit::topic');
        // }

    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TopicAgenda $agenda)
    {
        // if ($user->id == $agenda->created_by || in_array($user->position_id, ['1', '2', '3'])) {
        if ($user->id == $agenda->created_by) {
            return $user->can('update_submit::topic');
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TopicAgenda $agenda): bool
    {
        return $user->can('delete_submit::topic');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_submit::topic');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, TopicAgenda $agenda): bool
    {
        return $user->can('force_delete_submit::topic');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_submit::topic');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, TopicAgenda $agenda): bool
    {
        return $user->can('restore_submit::topic');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_submit::topic');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, TopicAgenda $agenda): bool
    {
        return $user->can('replicate_submit::topic');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_submit::topic');
    }

    /**
     * Determine whether the user can accept requests for the model.
     */
    public function acceptRequests(User $user)
    {
        // accept for super, system admin and user with position head of department
        if ($user->hasRole('Super Admin') || $user->hasRole('System Admin') || in_array($user->position_id, [3, 5])) {
            return true; // Allow these users to accept requests
        }

        return $user->can('accept_requests'); // Adjust permission check as needed
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
