<?php

namespace App\Nova\Flexible\Resolvers;

use App\Models\Document;
use App\Models\Item;
use App\Models\Receipt;
use Whitecube\NovaFlexibleContent\Layouts\Layout;
use Whitecube\NovaFlexibleContent\Value\ResolverInterface;

class PaymentMethodResolver implements ResolverInterface
{
    public function __construct(private string $kind = Document::KIND_PAYMENT_RECEIPT)
    {
    }

    /**
     * get the field's value
     *
     * @param  Receipt  $resource
     * @param  string  $attribute
     * @param  \Whitecube\NovaFlexibleContent\Layouts\Collection  $layouts
     *
     * @return \Illuminate\Support\Collection
     */
    public function get($resource, $attribute, $layouts)
    {
        /** @var \Illuminate\Database\Eloquent\Collection $items */
        $items = $resource->items()->where('data->kind', $this->kind)->get();

        if ($items->count() < 1) {
            $items->add(new Item(['data' => ['kind' => $this->kind]]));
        }

        return $items
            ->map(function ($item) use ($layouts) {
                $layout = $layouts->find($this->kind);

                if (!$layout) {
                    return null;
                }

                return $layout->duplicateAndHydrate($item->id, $item->toArray());
            })
            ->filter(fn($value) => !is_null($value));
    }

    /**
     * Set the field's value
     *
     * @param  mixed  $model
     * @param  string  $attribute
     * @param  \Illuminate\Support\Collection  $groups
     *
     * @return void
     */
    public function set($model, $attribute, $groups)
    {
        $model::saved(function (Receipt $receipt) use ($groups) {
            /** @var \Illuminate\Database\Eloquent\Collection $products */
            $products = request()
                ->user()
                ->team
                ->products()
                ->whereIn('id', $groups->pluck('product_id'))
                ->get();

            $items = $groups->map(function (Layout $item) use ($receipt, $products) {
                /** @var \App\Models\Product $product */
                $product = $products->firstWhere('id', $item->getAttribute('product_id'));

                $data['kind'] = $this->kind;

                foreach ($item->toArray() as $key => $value) {
                    if (!str_starts_with($key, 'data.')) {
                        continue;
                    }
                    $data[str_replace('data.', '', $key)] = $value;
                }

                return [
                    'currency'                 => $receipt->currency,
                    'data'                     => $data,
                    'document_id'              => $receipt->id,
                    'product_id'               => $product?->id,
                    'price'                    => $product?->price ?? $item->getAttribute('amount_paid'),
                    'title'                    => $item->getAttribute('title'),
                    'quantity'                 => $item->getAttribute('quantity') ?? 1,
                    'description'              => $item->getAttribute('description'),
                    'discount_rate'            => $item->getAttribute('discount_rate') ?? 0,
                    'amount_paid'              => $item->getAttribute('amount_paid') ?? 0,
                    'wallet_attribute_id'      => $item->getAttribute('wallet_attribute_id'),
                    'expire_at'                => $receipt->expire_at,
                    'emitted_at'               => $receipt->emitted_at,
                    'team_id'                  => $receipt->team_id,
                    'category_attribute_id'    => $receipt->category_attribute_id,
                    'subcategory_attribute_id' => $receipt->subcategory_attribute_id,
                    'provider_contact_id'      => $item->getAttribute('provider_contact_id') ?? $receipt->provider_contact_id,
                    'receiver_contact_id'      => $receipt->receiver_contact_id,
                    'author_user_id'           => $receipt->author_user_id,
                    'completed_by_user_id'     => $receipt->completed_by_user_id,
                ];
            })
                ->filter(fn($value) => !is_null($value));

            $receipt->items()->delete();
            $receipt->items()->createMany($items);

        });
    }
}
