<?php

namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class FlexibleItemLayout extends Layout
{
    /**
     * The layout's unique identifier
     *
     * @var string
     */
    protected $name = 'document_item';

    /**
     * The displayed title
     *
     * @var string
     */
    public function title()
    {
        return __('Item');
    }

    /**
     * Get the fields displayed by the layout.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make(__('Procedure'), 'product_id')
                ->searchable()
                ->displayUsingLabels()
                ->options(function () {
                    return request()->user()->team->products->mapWithKeys(fn($product
                    ) => [$product->id => $product->name." - ".number_format($product->price)]);
                })
                ->rules(['required', 'numeric']),
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
            Text::make(__('Description'), 'description')->nullable(),
//            Flexible::make(__('Additional information'), 'data')
//                ->addLayout(FlexibleDiagnosisLayout::class)
//                ->menu('flexible-drop-menu', [
//                    'class'=>'bg-info'
//                ])
//                ->limit(1)
//                ->button(__('Add detail'))
//                ->fullWidth()
        ];
    }

}
