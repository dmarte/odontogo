<?php

namespace App\Nova;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Catalog extends Resource
{
    const KIND = Attribute::KIND_CATALOG_ACCOUNTING;
    public static $displayInNavigation = false;
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

    public static $tableStyle = 'tight';
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
    ];

    public static $globallySearchable = false;

    public function title()
    {
        return "{$this->code} - {$this->name}";
    }

    public static function group()
    {
        return __('Settings');
    }

    public static function label()
    {
        return __('Catalogs');
    }

    public static function singularLabel()
    {
        return __('Catalog');
    }

    public static function scoutQuery(NovaRequest $request, $query)
    {
        $query
            ->where('kind', self::KIND)
            ->where(function ($query) use ($request) {
                $query->whereNull('team_id')
                    ->orWhere('team_id', $request->user()->member->team_id);
            });

        return $query;
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        $query
            ->where('kind', self::KIND)
            ->where(function ($query) use ($request) {
                $query->whereNull('team_id')
                    ->orWhere('team_id', $request->user()->member->team_id);
            });

        return $query;
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        $query
            ->where('kind', self::KIND)
//            ->whereIn('code', [1,2,3,4,5,6])
            ->where(function ($query) use ($request) {
                $query->whereNull('team_id')
                    ->orWhere('team_id', $request->user()->member->team_id);
            });

        return $query;
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        if ($request->route('resource') === Receipt::uriKey()) {
            $query->whereNull('team_id')
                ->where('kind', Attribute::KIND_CATALOG_ACCOUNTING)
                ->where('enabled', true)
                ->where('system_default', true)
                ->where('code', 'LIKE', \App\Models\Catalog::CATALOG_INCOME . '%')
                ->whereNotIn('code', [\App\Models\Catalog::CATALOG_INCOME])
                ->orderBy('code');

            return $query;
        }


        $query
            ->where('kind', self::KIND)
            ->where(function ($query) use ($request) {
                $query->whereNull('team_id')
                    ->orWhere('team_id', $request->user()->member->team_id);
            });

        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable()->onlyOnDetail(),
            Number::make(__('Code'), 'code')
                ->min(0)
                ->default(function () use ($request) {
                    return Attribute::where('team_id', $request->user()->team->id)
                            ->where('kind', self::KIND)
                            ->max('code') + 1;
                })
                ->rules([
                    'required',
                    'numeric',
                    Rule::unique('attributes', 'code')
                        ->where('team_id', $request->user()->team->id)
                        ->where('kind', self::KIND),
                ])
                ->sortable(),
            Text::make(__('Name'), 'name')
                ->creationRules([
                    'required',
                    'min:4',
                ]),
            Textarea::make(__('validation.attributes.description'), 'description'),
            Hidden::make(__('validation.attributes.team_id'), 'team_id')
                ->default(fn(NovaRequest $request) => $request->user()->team_id)
                ->showOnCreating(),
            BelongsTo::make(__('Parent'), 'parent', static::class)
                ->nullable()
                ->showCreateRelationButton()
                ->withoutTrashed()
                ->display(function ($model) {
                    return "{$model->code} - {$model->name}";
                }),
            Number::make(__('Debit'), 'amount_credit')->readonly(),
            Number::make(__('Credit'), 'amount_debit')->readonly(),
            Boolean::make(__('Enabled'), 'enabled')
                ->default(fn() => true)
                ->hideWhenCreating()
                ->hideFromIndex(),
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
     * @param  \Illuminate\Http\Request  $request
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
     * @param  \Illuminate\Http\Request  $request
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
     * @param  \Illuminate\Http\Request  $request
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
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
