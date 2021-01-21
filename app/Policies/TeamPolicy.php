<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use App\Pivots\TeamUser;
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
        return $team->user_id === $user->id || $user->teams()->wherePivot('team_id', $team->id)->exists();
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

        return $team
            ->users()
            ->wherePivot('team_id', $team->id)
            ->wherePivot('scopes->', TeamUser::SCOPE_MODIFY_TEAM, true)
            ->exists();
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
        //
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

    public function inviteMembers(User $user, Team $team)
    {
        return $team
            ->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('scopes->' . TeamUser::SCOPE_ADMIN, true)
            ->exists();
    }

    public function attachAnyUser()
    {
        return false;
    }

    public function attachUser(User $user, Team $team, User $target)
    {

        if ($user->id === $target->id || $target->id === $team->user_id) {
            return false;
        }

        return true;
    }

    public function detachUser(User $user, Team $team, User $target)
    {
        if ($user->id === $team->user_id || $target->id === $team->user_id) {
            return false;
        }

        if ($target->membership->scopes->where(TeamUser::SCOPE_ADMIN, true)->isNotEmpty()) {
            return false;
        }
        return true;
    }

}
