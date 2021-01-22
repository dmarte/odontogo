<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User   $user
     * @param \App\Models\Member $member
     *
     * @return mixed
     */
    public function view(User $user, Member $member)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->member->role->is_administrator;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User   $user
     * @param \App\Models\Member $member
     *
     * @return mixed
     */
    public function update(User $user, Member $member)
    {
        if ($member->is_team_owner || $member->id === $user->member_id || $member->role->is_administrator) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User   $user
     * @param \App\Models\Member $member
     *
     * @return mixed
     */
    public function delete(User $user, Member $member)
    {
        if ($member->is_team_owner && $member->user_id === $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User   $user
     * @param \App\Models\Member $member
     *
     * @return mixed
     */
    public function restore(User $user, Member $member)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User   $user
     * @param \App\Models\Member $member
     *
     * @return mixed
     */
    public function forceDelete(User $user, Member $member)
    {
        return false;
    }

}
