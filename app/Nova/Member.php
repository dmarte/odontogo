<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;

class Member extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Member::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'user.name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];
    public static $displayInNavigation = false;
    public static $globallySearchable = false;
    public static $searchable = false;
    public static $with = ['user'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            BelongsTo::make(__('User'), 'user', User::class),
            BelongsTo::make(__('Role'),'role', Role::class)->withoutTrashed(),
            BelongsTo::make(__('Team'),'team', Team::class),
            Badge::make(__('Status'), 'status')
                ->types([
                    \App\Models\Member::STATUS_JOINED => 'bg-success text-white',
                    \App\Models\Member::STATUS_INVITED => 'bg-warning text-black',
                ])
                ->labels(\App\Models\Member::statusesAsOptions()->toArray()),
            Date::make(__('Invited at'), 'invited_at')
            ->format('LLL')
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
