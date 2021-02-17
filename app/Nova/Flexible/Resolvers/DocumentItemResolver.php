<?php

namespace App\Nova\Flexible\Resolvers;

use App\Models\Budget;
use App\Models\Item;
use Whitecube\NovaFlexibleContent\Layouts\Layout;
use Whitecube\NovaFlexibleContent\Value\ResolverInterface;

class DocumentItemResolver implements ResolverInterface
{
    /**
     * get the field's value
     *
     * @param  \App\Models\Budget  $resource
     * @param  string  $attribute
     * @param  \Whitecube\NovaFlexibleContent\Layouts\Collection  $layouts
     *
     * @return \Illuminate\Support\Collection
     */
    public function get($resource, $attribute, $layouts)
    {
        return $resource->items
            ->map(function (Item $item) use ($layouts) {
                /** @var Layout $layout */
                $layout = $layouts->find('document_item');

                if (!$layout) {
                    return;
                }

                return $layout->duplicateAndHydrate($item->id, $item->toArray());
            })
            ->filter();
    }

    /**
     * Set the field's value
     *
     * @param  Budget  $model
     * @param  string  $attribute
     * @param  \Illuminate\Support\Collection  $groups
     *
     * @return string
     */
    public function set($model, $attribute, $groups)
    {

        $model::saved(function (Budget $budget) use ($groups) {
            /** @var \Illuminate\Database\Eloquent\Collection $products */
            $products = request()->user()->team->products()->whereIn('id', $groups->pluck('product_id'));

            $items = $groups->map(function (Layout $item) use ($budget, $products) {
                /** @var \App\Models\Product $product */
                $product = $products->where('id', $item->getAttribute('product_id'))->first();

                return [
                    'currency'                 => $budget->currency,
                    'document_id'              => $budget->id,
                    'product_id'               => $product->id,
                    'price'                    => $product->price,
                    'quantity'                 => $item->getAttribute('quantity'),
                    'description'              => $item->getAttribute('description'),
                    'expire_at'                => $budget->expire_at,
                    'emitted_at'               => $budget->emitted_at,
                    'team_id'                  => $budget->team_id,
                    'category_attribute_id'    => $budget->category_attribute_id,
                    'subcategory_attribute_id' => $budget->subcategory_attribute_id,
                    'provider_contact_id'      => $budget->provider_contact_id,
                    'receiver_contact_id'      => $budget->receiver_contact_id,
                    'author_user_id'           => $budget->author_user_id,
                    'comleted_by_user_id'      => $budget->completed_by_user_id,
                ];
            });

            $budget->items()->delete();
            $budget->items()->createMany($items);

        });
    }
}
