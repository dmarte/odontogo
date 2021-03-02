<?php

namespace App\Nova;

use App\Models\Product;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Service extends Resource
{
    public static $tableStyle = 'tight';
    public static $globalSearchResults = 20;
    public static $perPageOptions = [50, 100];
    public static $relatableSearchResults = 20;
    public static $preventFormAbandonment = true;
    public static $showColumnBorders = true;
    public static $model = Product::class;
    public static $title = 'name';
    public static $priority = 3;
    public static $search = [
        'name',
        'code',
    ];

    public function subtitle()
    {
        return '$ ' . number_format($this->price);
    }

    public static function softDeletes()
    {
        return false;
    }

    public static function group()
    {
        return __('Branch');
    }

    public static function label()
    {
        return __('Services');
    }

    public static function singularLabel()
    {
        return __('Service');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('team_id', $request->user()->member->team_id);
    }

    public static function scoutQuery(NovaRequest $request, $query)
    {
        return $query->where('team_id', $request->user()->member->team_id);
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return $query->where('team_id', $request->user()->member->team_id);
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query->where('team_id', $request->user()->member->team_id);
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
            ID::make(__('ID'), 'id')->onlyOnDetail(),
            Text::make(__('Code'), 'code')->hideWhenCreating()->hideWhenUpdating()->sortable(),
            Text::make(__('Service'), 'name')
                ->rules([
                    'required',
                    'min:3',
                ])
                ->sortable(),
            Select::make(__('Currency'), 'currency')
                ->options(function () {
                    return collect(config('currencies'))
                        ->mapWithKeys(fn($label, $currency) => [$currency => __("{$currency} - {$label}")])
                        ->toArray();
                })
                ->default(fn($request) => $request->user()->currency)
                ->searchable()
                ->onlyOnForms()
                ->sortable()
                ->rules([
                    'required',
                    'size:3',
                ]),
            Currency::make(__('Price'), 'price')
                ->min(0)
                ->rules([
                    'required',
                    'min:0',
                ])
                ->currency($this->resource?->currency ?? config('nova.currency'))
                ->locale('en')
                ->sortable(),
            BelongsTo::make(__('Area'), 'career', Careers::class)
                ->withoutTrashed()
                ->sortable()
                ->searchable(),
            Hidden::make('team_id')
                ->default(fn($request) => $request->user()->member->team_id)
                ->hideWhenUpdating(),
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
