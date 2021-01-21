<?php

namespace App\Observers;

use App\Models\Team;
use App\Pivots\TeamUser;

class TeamObserver
{
    /**
     * Handle the Team "created" event.
     *
     * @param \App\Models\Team $team
     *
     * @return void
     */
    public function created(Team $team)
    {
        $team->users()->attach($team->user_id, [
            'scopes' => collect(TeamUser::SCOPES)->mapWithKeys(fn($scope)=> [$scope => true])->toArray(),
            'status' => TeamUser::STATUS_JOINED,
        ]);
    }

    /**
     * Handle the Team "updated" event.
     *
     * @param \App\Models\Team $team
     *
     * @return void
     */
    public function updated(Team $team)
    {
        //
    }

    /**
     * Handle the Team "deleted" event.
     *
     * @param \App\Models\Team $team
     *
     * @return void
     */
    public function deleted(Team $team)
    {
        //
    }

    /**
     * Handle the Team "restored" event.
     *
     * @param \App\Models\Team $team
     *
     *
     * @return void
     */
    public function restored(Team $team)
    {
        //
    }

    /**
     * Handle the Team "force deleted" event.
     *
     * @param \App\Models\Team $team
     *
     * @return void
     */
    public function forceDeleted(Team $team)
    {
        //
    }
}
