<?php


namespace App\Nova\Fields;


use App\Models\User;
use App\Models\Member;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;

class MembershipFields
{
    public function permissions(): Collection
    {
        return Member::scopesAsOptions();
    }

    public function __invoke(): array
    {
        return [
            Badge::make(__('validation.attributes.status'), 'status')
                ->types([
                    Member::STATUS_JOINED  => 'bg-success text-white',
                    Member::STATUS_INVITED => 'bg-warning text-black',
                ])
                ->labels(Member::statusesAsOptions()->toArray())
            ,
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
