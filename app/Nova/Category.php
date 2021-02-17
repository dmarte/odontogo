<?php

namespace App\Nova;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Category extends Resource
{
    const KIND = Attribute::KIND_GENERAL_CATEGORY;
    public static $globallySearchable = false;
    public static $model = Attribute::class;
    public static $title = 'name';
    public static $search = [
        'name',
    ];

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

    public static function scoutQuery(NovaRequest $request, $query)
    {
        $query
            ->where('kind', self::KIND)
            ->where('team_id', $request->user()->team->id)
        ;

        return $query;
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        $query
            ->where('kind', self::KIND)
            ->where('team_id', $request->user()->member->team_id);

        return $query;
    }

    public static function indexQuery(NovaRequest $request, $query)
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
            Textarea::make(__('Description'), 'description'),
            Hidden::make('team_id')
                ->default($request->user()->team->id)
                ->showOnCreating(),
            Boolean::make(__('Enabled'), 'enabled')
                ->default(fn() => true)
                ->hideWhenCreating(),
            Hidden::make('enabled')
                ->default(fn() => 1)
                ->showOnCreating(),
            Hidden::make('kind')
                ->onlyOnForms()
                ->default(fn() => self::KIND),
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
