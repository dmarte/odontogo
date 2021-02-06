<?php

namespace App\Observers;

use App\Models\Attribute;
use App\Models\Document;
use App\Models\Member;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class TeamObserver
{
    public function createBaseInsurances(Team $team): void
    {

        $insurances = config("ogo.{$team->country}.insurances");

        if (!is_array($insurances)) {
            return;
        }

        foreach ($insurances as $insurance) {
            $team
                ->insurances()
                ->firstOrCreate([
                    'name' => $insurance,
                    'kind' => Attribute::KIND_DENTAL_INSURANCE,
                    'author_user_id'=> $team->user_id
                ]);
        }
    }

    public function createBaseSources(Team $team): void
    {

        $sources = config("ogo.sources");

        if (!is_array($sources)) {
            return;
        }

        foreach ($sources as $source) {
            $team->sources()->firstOrCreate([
                'name'           => $source,
                'kind'           => Attribute::KIND_AD_SOURCE,
                'author_user_id' => $team->user_id,
            ]);
        }
    }

    public function createBaseDiagnosis(Team $team): void
    {

        $diagnosis = config("ogo.diagnosis");

        if (!is_array($diagnosis)) {
            return;
        }

        foreach ($diagnosis as $name) {
            $team->diagnosis()->firstOrCreate([
                'name'           => $name,
                'kind'           => Attribute::KIND_DENTAL_DIAGNOSIS,
                'author_user_id' => $team->user_id,
            ]);
        }
    }

    public function createBaseProcedures(Team $team): void
    {

        $procedures = config("ogo.procedures");

        if (!is_array($procedures)) {
            return;
        }

        $career = $team->careers()->first();

        foreach ($procedures as $procedure) {
            $team->products()->firstOrCreate([
                'name'                => $procedure,
                'price'               => 0,
                'currency'            => $team->currency,
                'author_user_id'      => $team->user_id,
                'career_attribute_id' => $career->id ?? null,
            ]);
        }
    }

    public function createBaseCareers(Team $team): void
    {

        $careers = config("ogo.careers");

        if (!is_array($careers)) {
            return;
        }

        foreach ($careers as $career) {
            $team->careers()->firstOrCreate([
                'name'           => $career,
                'kind'           => Attribute::KIND_DENTAL_CAREER,
                'author_user_id' => $team->user_id,
            ]);
        }
    }

    public function createBaseSequences(Team $team): void
    {
        $types = Document::KINDS;

        foreach ($types as $type) {
            if ($team->sequencesNotExpired()->where("types->{$type}", true)->exists()) {
                continue;
            }

            $team->sequences()->create([
                'title' => __("document.{$type}"),
                'subtitle' => null,
                'prefix' => $type,
                'types' => [$type => true],
                'length' => 6,
                'initial_counter' => 1,
                'maximum' => 2000,
                'expire_at' => now()->addMonths(12)->format('Y-m-d'),
                'author_user_id' => $team->user_id,
            ]);
        }
    }

    public function creating(Team $team) {

        if (is_null($team->currency)) {

            $team->currency = config('nova.currency');

        }

    }

    public function created(Team $team)
    {
        DB::transaction(function () use ($team) {

            $scopes = collect(Member::SCOPES)->mapWithKeys(fn($scope) => [$scope => true])->toArray();

            $team
                ->roles()
                ->createMany(
                    collect(Member::LEVELS)
                        ->map(function ($level) use ($scopes) {
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
                'user_id'       => $team->user_id,
                'role_id'       => $role->id,
                'status'        => Member::STATUS_JOINED,
                'is_team_owner' => true,
            ]);

            $team->user->update([
                'team_id'   => $team->id,
                'member_id' => $member->id,
            ]);

            $member->activate();

            $this->createBaseCareers($team);
            $this->createBaseDiagnosis($team);
            $this->createBaseInsurances($team);
            $this->createBaseSources($team);
            $this->createBaseProcedures($team);
            $this->createBaseSequences($team);
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
