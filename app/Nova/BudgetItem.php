<?php

namespace App\Nova;

use App\Models\Item as Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;
use Whitecube\NovaFlexibleContent\Flexible;

class BudgetItem extends Resource
{
    use SearchesRelations;

    public static $displayInNavigation = false;
    public static $searchable = false;
    public static $globalSearchLink = false;
    public static $perPageViaRelationship = 100;
    public static $showColumnBorders = true;
    public static $with = ['document'];
    public static $tableStyle = 'tight';
    public static $model = Model::class;
    public static $title = 'title';
    public static $searchResults = [
        'product' => ['name', 'code'],
    ];
    public static $search = [];

    public static function softDeletes()
    {
        return false;
    }

    public static function label()
    {
        return __('Procedures');
    }

    public static function singularLabel()
    {
        return __('Procedure');
    }

    public static function createButtonLabel()
    {
        return __('Add');
    }

    public function fieldsForIndex()
    {
        return [
            Stack::make(__('Procedure'), [
                BelongsTo::make(__('Procedure'), 'product', Service::class)
                    ->withoutTrashed()
                    ->searchable()
                    ->viewable(false),
                Text::make(__('Notes'), 'description')->nullable(),
            ]),
            Number::make(__('Quantity'), 'quantity')->textAlign('center'),
            Number::make(__('Subtotal'), 'subtotal')->textAlign('right'),
            Number::make(__('Discounts'), 'discounts')->textAlign('right'),
            Number::make(__('Taxes'), 'taxes')->textAlign('right'),
            Currency::make(__('Total'), 'total')
                ->textAlign('right')
                ->locale('en-US')
                ->currency($this->resource?->document?->currency ?? config('nova.currency')),
        ];
    }


    public function fieldsForCreate()
    {
        return [
            BelongsTo::make(__('Budget'), 'document', Budget::class)
                ->withoutTrashed()
                ->withSubtitles()
                ->searchable()
                ->readonly(),
            BelongsTo::make(__('Procedure'), 'product', Service::class)
                ->withoutTrashed()
                ->searchable()
                ->displayUsing(function (Service $product) {
                    return $product->name." - ".number_format($product->price);
                }),
            Number::make(__('Quantity'), 'quantity')
                ->default(0)
                ->rules(['required', 'min:0']),
            Select::make(__('Discount'), 'discount_rate')
                ->default(0)
                ->options([
                    0  => __('No discount'),
                    5  => '5%',
                    10 => '10%',
                    15 => '15%',
                    25 => '25%',
                    30 => '30%',
                    35 => '35%',
                    40 => '40%',
                    45 => '45%',
                    50 => '50%',
                    55 => '55%',
                    60 => '60%',
                    65 => '65%',
                    70 => '70%',
                    75 => '75%',
                ]),
            Text::make(__('Notes'), 'description')->nullable(),
        ];
    }

    public function fieldsForUpdate()
    {
        return [
            BelongsTo::make(__('Budget'), 'document', Budget::class)
                ->withoutTrashed()
                ->withSubtitles()
                ->searchable()
                ->readonly(),
            BelongsTo::make(__('Procedure'), 'product', Service::class)
                ->withoutTrashed()
                ->searchable()
                ->displayUsing(function (Service $product) {
                    return $product->name." - ".number_format($product->price);
                }),
            Number::make(__('Quantity'), 'quantity')
                ->default(0)
                ->rules(['required', 'min:0']),
            Select::make(__('Discount'), 'discount_rate')
                ->default(0)
                ->options([
                    0  => __('No discount'),
                    5  => '5%',
                    10 => '10%',
                    15 => '15%',
                    25 => '25%',
                    30 => '30%',
                    35 => '35%',
                    40 => '40%',
                    45 => '45%',
                    50 => '50%',
                    55 => '55%',
                    60 => '60%',
                    65 => '65%',
                    70 => '70%',
                    75 => '75%',
                ]),
            Text::make(__('Notes'), 'description')->nullable(),
            Flexible::make(__('Additional information'), 'additional')
                ->addLayout(''),
        ];
    }

    public function fieldsForDetail()
    {
        return [
            Heading::make(__('Summary')),
            Number::make(__('Quantity'), 'quantity'),
            Number::make(__('Price'), 'price')->displayUsing(fn($value) => number_format($value)),
            Number::make(__('Amount'), 'amount')->displayUsing(fn($value) => number_format($value)),
            Stack::make(__('Discount'), [
                Number::make('discounts')->displayUsing(fn($value) => number_format($value)),
                Number::make('discount_rate')->displayUsing(function ($value) {
                    return "({$value}%)";
                }),
            ]),
            Number::make(__('Subtotal'), 'subtotal')->displayUsing(fn($value) => number_format($value)),
            Number::make(__('Taxes'), 'taxes')->displayUsing(fn($value) => number_format($value)),
            Currency::make(__('Total'), 'total')
                ->locale('en-US')
                ->currency($this->resource->currency),

            // Details
            Heading::make(__('Details')),
            BelongsTo::make(__('Budget'), 'document', Budget::class)
                ->withoutTrashed()
                ->withSubtitles()
                ->searchable()
                ->readonly(),
            BelongsTo::make(__('Procedure'), 'product', Service::class)
                ->withoutTrashed()
                ->searchable()
                ->displayUsing(function (Service $product) {
                    return $product->name." - ".number_format($product->price);
                }),
            Text::make(__('Notes'), 'description'),
            // Administrative area
            Heading::make(__('Administrative area')),
            BelongsTo::make(__('Created by'), 'author', User::class),
            Date::make(__('Emitted at'), 'emitted_at')->format('DD/MM/YYYY'),
            Boolean::make(__('Approved by the patient'), 'verified'),
            Date::make(__('Approved at'), 'approved_at')->format('DD/MM/YYYY'),
        ];
    }

    public function fields(Request $request)
    {
        return [
            BelongsTo::make(__('Budget'), 'document', Budget::class)
                ->withoutTrashed()
                ->withSubtitles()
                ->searchable()
                ->readonly(),
            BelongsTo::make(__('Procedure'), 'product', Service::class)
                ->withoutTrashed()
                ->searchable()
                ->displayUsing(function (Service $product) {
                    return $product->name." - ".number_format($product->price);
                }),
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
