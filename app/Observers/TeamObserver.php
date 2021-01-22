<?php

namespace App\Observers;

use App\Models\Member;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

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
        DB::transaction(function() use($team) {

            $scopes = collect(Member::SCOPES)->mapWithKeys(fn($scope) => [$scope => true])->toArray();

            $team
                ->roles()
                ->createMany(
                    collect(Member::LEVELS)
                    ->map(function ($level) use($scopes) {
                        return new Role([
                            'name'   => __("User level {$level}"),
                            'scopes' => $scopes,
                            'level'  => $level,
                        ]);
                    })
                        ->toArray()
                );

            /* @var $role \App\Models\Role */
            $role = $team->roles()->where('level', Member::LEVEL_ADMINISTRATOR)->first();

            /* @var $member Member */
            $member = $team->members()->create([
                'user_id' => $team->user_id,
                'role_id' => $role->id,
                'status'  => Member::STATUS_JOINED,
                'is_team_owner'=> true
            ]);

            $team->user->update([
                'team_id'   => $team->id,
                'member_id' => $member->id,
            ]);

            $member->activate();
        });
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
