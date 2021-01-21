<?php

namespace App\Nova;

use App\Nova\Actions\UserInvitationAction;
use App\Nova\Fields\TeamUserFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Team extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Team::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->whereHas('users', function(Builder $builder) use($request){
            $builder->where('user_id', $request->user()->id);
        });
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query->whereHas('users', function(Builder $builder) use($request){
            $builder->where('user_id', $request->user()->id);
        });
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('validation.attributes.name'))->help(__('Put the name of the team')),
            BelongsToMany::make(__('validation.attributes.user_id'), 'users')
                ->searchable()
                ->fields(new TeamUserFields),
            Hidden::make('user_id')->default($request->user()->id)->onlyOnForms()
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            UserInvitationAction::make()->canSeeWhen('inviteMembers')
        ];
    }
}
