<?php

namespace App\Nova\Actions;

use App\Models\Team;
use App\Models\User;
use App\Pivots\TeamUser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Text;

class UserInvitationAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $onlyOnDetail = true;

    public function name()
    {
        return __('Invite a user');
    }

    public function confirmButtonText($text)
    {
        return __('Invite');
    }

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection    $models
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (Team $team) use ($fields) {

            $team->inviteByEmail($fields->name, $fields->email, $fields->scopes);

        });

        return Action::message(__('User invited'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make(__('validation.attributes.name'),'name')
                ->creationRules([
                    'required',
                ]),
            Text::make(__('validation.attributes.email'), 'email')
                ->creationRules([
                    'required',
                    'email',
                    Rule::unique(User::class, 'email'),
                ]),
            BooleanGroup::make(__('Permissions'), 'scopes')
            ->options(TeamUser::scopesAsOptions())
            ->default(function (){
                return [
                    TeamUser::SCOPE_CONTACTS_ADD => true,
                ];
            })
        ];
    }
}
