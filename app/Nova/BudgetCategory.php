<?php

namespace App\Nova;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class BudgetCategory extends Resource
{
    const KIND = Attribute::KIND_BUDGET_CATEGORY;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Attribute::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    public static $displayInNavigation = false;
    public static $globallySearchable = false;

    public static function group()
    {
        return __('Settings');
    }

    public static function label()
    {
        return __('Categories');
    }

    public static function singularLabel()
    {
        return __('Category');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        $query
            ->where('kind', self::KIND)
            ->where('team_id', $request->user()->member->team_id);

        return $query;
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return $query
            ->where('kind', self::KIND)
            ->where('team_id', $request->user()->member->team_id);
    }

    public static function scoutQuery(NovaRequest $request, $query)
    {
        $query
            ->where('kind', self::KIND)
            ->where('team_id', $request->user()->member->team_id);

        return $query;
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        $query
            ->where('kind', self::KIND)
            ->where('team_id', $request->user()->member->team_id);

        return $query;
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
            ID::make(__('ID'), 'id')->sortable()->onlyOnDetail(),
            Text::make(__('Name'), 'name')
                ->creationRules([
                    'required',
                    'min:4',
                ]),
            Textarea::make(__('validation.attributes.description'), 'description'),
            Boolean::make(__('Enabled'), 'enabled')
                ->default(fn() => true)
                ->hideWhenCreating(),
            Hidden::make('team_id')
                ->default(fn() => $request->user()->member->team_id)
                ->onlyOnForms()
                ->rules([
                    'required',
                ]),
            Hidden::make('enabled')
                ->default(fn() => 1)
                ->showOnCreating(),
            Hidden::make('kind')
                ->onlyOnForms()
                ->default(fn() => self::KIND),
        ];
    }

    /**
     * Since we can only view the list of items,
     * after create or update we will be redirecting
     * to the list of items.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param \Laravel\Nova\Resource                  $resource
     *
     * @return string
     */
    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/' . static::uriKey();
    }

    /**
     * Since we can only view the list of items,
     * after create or update we will be redirecting
     * to the list of items.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param \Laravel\Nova\Resource                  $resource
     *
     * @return string
     */
    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/' . static::uriKey();
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
