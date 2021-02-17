<?php

namespace App\Nova;

use App\Models\Item;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class BudgetItem extends Resource
{
    public static $displayInNavigation=false;
    public static $searchable=false;
    public static $globalSearchLink=false;
    public static $perPageViaRelationship=100;
    public static $showColumnBorders=true;
    public static $model = Item::class;
    public static $title = 'id';
    public static $search = [];

    public function fields(Request $request)
    {
        return [
            BelongsTo::make(__('Budget'), 'document', Budget::class),
            BelongsTo::make(__('Procedure'), 'product', Service::class)
                ->withoutTrashed()
                ->searchable(),
            Number::make(__('Discount'), 'discounts'),
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
        return [];
    }
}
