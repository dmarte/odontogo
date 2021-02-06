<?php

namespace App\Nova;

use App\Models\Attribute;
use App\Nova\Fields\AttributesFields;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Source extends Resource
{
    public static $globallySearchable = false;

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

    public static function group()
    {
        return __('Settings');
    }

    public static function label()
    {
        return __('Sources');
    }

    public static function singularLabel()
    {
        return __('Source');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        $query
            ->where('kind', Attribute::KIND_AD_SOURCE)
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
            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('validation.attributes.name'), 'name')
                ->creationRules([
                    'required',
                    'min:4',
                ]),
            Textarea::make(__('validation.attributes.description'), 'description'),
            BelongsTo::make(__('validation.attributes.team_id'), 'team', Team::class)
                ->default(fn(NovaRequest $request) => $request->user()->team_id),
            Boolean::make(__('validation.attributes.enabled'), 'enabled')
                ->default(fn() => true),
            Hidden::make('kind')
                ->onlyOnForms()
                ->default(fn() => Attribute::KIND_AD_SOURCE),
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
