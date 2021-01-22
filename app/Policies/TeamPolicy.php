<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
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
     * @param \App\Models\User $user
     * @param \App\Models\Team $team
     *
     * @return mixed
     */
    public function view(User $user, Team $team)
    {
        return $team->user_id === $user->id || $user->memberships->where('team_id', $team->id)->isNotEmpty();
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
        return $user->invited_by_user_id === null;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Team $team
     *
     * @return mixed
     */
    public function update(User $user, Team $team)
    {
        if ($user->id === $team->user_id) {
            return true;
        }

        return $user
            ->memberships
            ->where('team_id', $team->id)
            ->where('role.scopes.' . Member::SCOPE_MODIFY_TEAM, true)
            ->isNotEmpty();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Team $team
     *
     * @return mixed
     */
    public function delete(User $user, Team $team)
    {
        return $user->id === $team->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Team $team
     *
     * @return mixed
     */
    public function restore(User $user, Team $team)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Team $team
     *
     * @return mixed
     */
    public function forceDelete(User $user, Team $team)
    {
        //
    }

    public function invite(User $user, Team $team)
    {
        return $user
            ->memberships
            ->where('team_id', $team->id)
            ->where('role.level', '<', Member::LEVEL_MANAGER)
            ->whereIn('role.scopes.' . Member::SCOPE_INVITE_MEMBER, true)
            ->isNotEmpty()
            ;
    }

    public function addMember()
    {
        return false;
    }
}
