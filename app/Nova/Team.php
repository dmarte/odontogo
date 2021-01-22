<?php

namespace App\Nova;

use App\Nova\Actions\UserInvitationAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
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
    public static $title = 'name';

    public static $searchable = false;

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
        return $query->whereHas('members', function (Builder $builder) use ($request) {
            $builder->where('user_id', $request->user()->id);
        });
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query->whereHas('members', function (Builder $builder) use ($request) {
            $builder->where('user_id', $request->user()->id);
        });
    }

    public static function group()
    {
        return __('Administration');
    }

    public static function label()
    {
        return __('Teams');
    }
    
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
            Text::make(__('validation.attributes.name')),
            HasMany::make(__('validation.attributes.user_id'), 'members', Member::class),
            Hidden::make('user_id')->default($request->user()->id)->onlyOnForms(),
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
        return [
            (new UserInvitationAction($this->resource))
                ->confirmButtonText(__('Invite'))
                ->cancelButtonText(__('Cancel'))
                ->onlyOnDetail()
                ->showOnTableRow()
                ->canSee(function ($request) {
                    if ($request instanceof ActionRequest) {
                        return true;
                    }

                    if (!$this->resource instanceof \App\Models\Team) {
                        return false;
                    }

                    return $request->user()->can('invite', $this->resource);
                }),
        ];
    }
}
