<?php

namespace App\Nova;

use App\Models\Product;
use Exception;
use Gldrenthe89\NovaCalculatedField\BroadcasterBelongsToField;
use Gldrenthe89\NovaCalculatedField\BroadcasterField;
use Gldrenthe89\NovaCalculatedField\ListenerField;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Techouse\IntlDateTime\IntlDateTime;
use Titasgailius\SearchRelations\SearchesRelations;

class ReceiptTransaction extends Resource
{
    use SearchesRelations;

    public static $model = \App\Models\ReceiptTransaction::class;
    public static $tableStyle = 'tight';
    public static $displayInNavigation = false;
    public static $title = 'code';
    public static $search = [];

    public static function label()
    {
        return __('Transactions');
    }

    public static function singularLabel()
    {
        return __('Distribution');
    }

    public function fields(Request $request)
    {
        return [
            BelongsTo::make(__('Receipt'), 'receipt', Receipt::class)
                ->searchable()
                ->withSubtitles()
                ->withoutTrashed()
                ->default(fn() => $request->get('viaResourceId'))
                ->required()
                ->rules(['required'])
                ->hideFromIndex(),
            IntlDateTime::make(__('Paid at'), 'emitted_at')
                ->format('D MMM YYYY')
                ->hideUserTimeZone()
                ->rules(['required', 'date'])
                ->hideWhenUpdating()
                ->hideWhenCreating(),
            BelongsTo::make(__('Doctor'), 'provider', Doctor::class)
                ->searchable()
                ->required()
                ->rules([
                    'required',
                    'numeric',
                ]),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class)
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            BroadcasterBelongsToField::make(__('Procedure'), 'product', Service::class)
                ->searchable()
                ->withSubtitles()
                ->withoutTrashed()
                ->nullable()
                ->broadcastTo('product')
                ->hideFromIndex(),
            BroadcasterField::make(__('Quantity'), 'quantity')
                ->required()->rules(['required', 'numeric'])
                ->default(1)
                ->withMeta(['type' => 'number'])
                ->broadcastTo('quantity')
                ->hideFromIndex(),
            ListenerField::make(__('Payment'), 'amount_paid')
                ->displayUsing(fn($value) => number_format($value))
                ->help(__('Let this field in blank to auto-distribute the amount based on procedure price.'))
                ->listensTo(['product','quantity'])
                ->calculateWith(function (Collection $collection) {
                    return Product::find($collection->get('product'))->price * (float) $collection->get('quantity', 1);
                })
                ->rules([
                    'bail',
                    'required',
                    'numeric',
                    'min:1',
                    function ($attribute, $value, $fail) use ($request) {

                    if ($this->resource && $this->resource->receipt) {
                        $receipt = $this->resource->receipt;
                    } else {
                        try {
                            /** @var \App\Models\Expense $receipt */
                            $receipt = \App\Models\Receipt::findOrFail($request->get('viaResourceId'));

                        } catch (Exception $exception) {

                            $fail(__('validation.exists', ['attribute' => __('Document')]));

                            return;
                        }
                    }

                        if ($request->route()->hasParameter('resourceId')) {

                            $sum = (float) $receipt
                                ->items()
                                ->whereNotIn('id', [$request->route('resourceId')])
                                ->sum('amount_paid');
                        } else {
                            $sum = (float) $receipt
                                ->items()
                                ->sum('amount_paid');
                        }

                        if ($sum >= $receipt->amount_paid || ($sum + $value) > $receipt->amount_paid) {
                            $fail(__('You cannot add this distribution because will exceed the receipt total paid.'));
                        }

                    },
                ]),
            Number::make(__('Amount to pay'), 'total')
                ->hideWhenUpdating()
                ->hideWhenCreating()
                ->displayUsing(fn($value) => number_format($value)),
            Number::make(__('Pending'), 'pending')
                ->hideWhenUpdating()
                ->hideWhenCreating()
                ->displayUsing(fn($value) => number_format($value)),
            Text::make(__('Notes'), 'description')->help(__('Additional information for reference.'))
                ->hideFromIndex(),
        ];
    }
}
