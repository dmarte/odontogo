<?php


namespace App\Nova\Fields;


use App\Models\User;
use App\Pivots\TeamUser;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;

class TeamUserFields
{
    public function permissions(): Collection
    {
        return TeamUser::scopesAsOptions();
    }

    public function __invoke(): array
    {
        return [
            Badge::make(__('validation.attributes.status'), 'status')
                ->types([
                    TeamUser::STATUS_JOINED  => 'bg-success text-white',
                    TeamUser::STATUS_INVITED => 'bg-warning text-black',
                ])
                ->labels(TeamUser::statusesAsOptions()->toArray()),
            Date::make(__('validation.attributes.invited_at'), 'invited_at')
                ->format('LLL')
                ->hideWhenUpdating()
                ->hideWhenCreating(),
            BooleanGroup::make(__('Permissions'), 'scopes')
                ->options($this->permissions())
                ->hideFalseValues()
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            BooleanGroup::make(__('Permissions'), 'scopes')
                ->options($this->permissions())
                ->onlyOnForms()
                ->readonly(function (NovaRequest $request) {
                    if ($request->route('resource') === 'teams' && $request->route('relatedResource') === 'users') {
                        /* @var $team \App\Models\Team */
                        $team = $request->findResourceOrFail()->model();

                        $target = User::find($request->route('relatedResourceId'));

                        return !$request->user()->can('attachUser',[ $team, $target]);
                    }

                    return false;
                }),
        ];
    }
}
