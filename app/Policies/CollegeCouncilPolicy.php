<?php

namespace App\Policies;

use App\Models\CollegeCouncil;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\DB;

class CollegeCouncilPolicy
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
    public function view(User $user, CollegeCouncil $collegeCouncil)
    {
        return true;

    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        $depId = DB::table('department__councils')
            ->where('user_id', $user->id)
            ->get()->first();

        if (is_null($depId)) {
            // Handle the case where no department council record is found
            return false;
        }
        // dump($depId->department_id);
        if (in_array($user->position_id, ['2', '3']) && $depId->department_id) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CollegeCouncil $collegeCouncil)
    {
        // Check if the logged in user is a Dean
        if ($user->position_id == 5) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CollegeCouncil $collegeCouncil)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CollegeCouncil $collegeCouncil)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CollegeCouncil $collegeCouncil)
    {
        //
    }
}
