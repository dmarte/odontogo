<?php

namespace App\Nova;

use App\Models\Item as Model;
use App\Models\Product;
use Gldrenthe89\NovaCalculatedField\BroadcasterSelectField;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class ReceiptItem extends Resource
{
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

    public function fields(Request $request)
    {
        return [
            Hidden::make('document_id')->default($request->get('viaResourceId')),
            Hidden::make('discount_rate')->default(0),
            BelongsTo::make(__('Doctor'), 'provider', Doctor::class)
                ->help(__('Please indicate the doctor that completes the procedure to be paid.'))
                ->showCreateRelationButton()
                ->searchable()
                ->withSubtitles(),
            BroadcasterSelectField::make(__('Procedure'), 'product_id')
                ->help(__('The procedure that is being paid.'))
                ->options(function() use($request) {
                    return $request->user()->team->products->mapWithKeys(function(Product $product){
                        return [ $product->getKey() => "{$product->name} - ".number_format($product->price) ];
                    });
                })
                ->searchable()
                ->rules(['required', 'numeric']),
            Number::make(__('Quantity'), 'quantity')
                ->default(1)
                ->rules(['required', 'min:0']),
            Number::make(__('Amount paid'), 'amount_paid'),
            Text::make(__('Notes'), 'description')->nullable(),
        ];
    }
}
