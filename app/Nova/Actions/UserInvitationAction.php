<?php

namespace App\Nova\Actions;

use App\Models\Member;
use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class UserInvitationAction extends Action
{
    use InteractsWithQueue, Queueable;

    public function __construct(private Team $team)
    {
    }

    public function name()
    {
        return __('Invite a user');
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

            $team->inviteByEmail($fields->name, $fields->email, $fields->role);

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
            Text::make(__('validation.attributes.name'), 'name')
                ->creationRules([
                    'required',
                ]),
            Text::make(__('validation.attributes.email'), 'email')
                ->creationRules([
                    'required',
                    'email',
                    Rule::unique(User::class, 'email'),
                ]),
            Select::make(__('Role'), 'role')
                ->options(function () {
                    return $this
                        ->team
                        ->roles()
                        ->where('level', '>=', auth()->user()->member->role->level)
                        ->pluck('name', 'id');
                })
                ->creationRules([
                    'required',
                    'numeric',
                ])
            ->default(function(){
                return auth()->user()->member->role->id;
            }),
            BooleanGroup::make(__('Permissions'), 'scopes')
                ->options(Member::scopesAsOptions())
                ->default(function () {
                    return [
                        Member::SCOPE_CONTACTS_ADD    => true,
                        Member::SCOPE_CONTACTS_MODIFY => true,
                    ];
                }),
        ];
    }
}
