<?php

namespace App\Nova\Flexible\Layouts;

use App\Models\Document;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class PaymentMethodLayout extends Layout
{
    // Important this name should match with the resolver to find the
    // matcher for items.
    protected $name = Document::KIND_PAYMENT_RECEIPT;

    public function title()
    {
        return __('Payment distribution');
    }

    public function fields()
    {
        return [
            Number::make(__('Payment amount'), 'amount_paid')
                ->rules([
                    'required',
                    'numeric',
                    'min:1',
                ])
                ->displayUsing(fn($value) => number_format((float) $value)),
//            Number::make(__('Pending'), 'balance')->default(0),
            Select::make(__('Payment method'), 'data.method')
                ->default('cash')
                ->options(function () {
                    return [
                        'cash'          => __('Cash'),
                        'check'         => __('Check'),
                        'bank_transfer' => __('Bank transfer'),
                        'credit_card'   => __('Credit card'),
                        'credit_note'   => __('Credit note'),
                    ];
                })
                ->displayUsingLabels(),
            Text::make(__('Confirmation number or check'), 'data.confirmation_number')->help(__('Only if applicable')),
            Select::make(__('Procedure'), 'product_id')
                ->nullable()
                ->searchable()
                ->options(function () {
                    return request()->user()->team->products->mapWithKeys(fn($product) => [$product->getKey() => "{$product->name} - ".number_format($product->price)]);
                })
                ->displayUsingLabels(),
            Number::make(__('Quantity'), 'quantity')->default(1),
            Text::make(__('Concept'), 'title')->rules(['nullable'])
            ,
            Select::make(__('Doctor'), 'provider_contact_id')
                ->rules([
                    'required',
                    'numeric',
                    'exists:contacts,id',
                ])
                ->options(function () {
                    return request()->user()->team->doctors->mapWithKeys(function ($doctor) {
                        return [$doctor->getKey() => "{$doctor->code} - {$doctor->name}"];
                    });
                })
                ->displayUsingLabels(),
            Select::make(__('Wallet'), 'wallet_attribute_id')
                ->rules([
                    'required',
                    'numeric',
                    'exists:attributes,id',
                ])
                ->options(function () {
                    return request()->user()->team->wallets->mapWithKeys(function ($wallet) {
                        return [$wallet->getKey() => "{$wallet->code} - {$wallet->name}"];
                    });
                })
                ->displayUsingLabels(),
        ];
    }
}
