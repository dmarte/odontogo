<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function creating(Product $product) : void
    {
        $this->code($product);
    }

    private function code(Product &$product) : void
    {
        $product->counter = Product::where('team_id', $product->team_id)
                ->where('prefix', $product->prefix)
                ->orderBy('counter')->max('counter') + 1;

        $product->code = $product->prefix . str_pad($product->counter,6,'0', STR_PAD_LEFT);

    }

    /**
     * Handle the Product "created" event.
     *
     * @param \App\Models\Product $product
     *
     * @return void
     */
    public function created(Product $product)
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param \App\Models\Product $product
     *
     * @return void
     */
    public function updated(Product $product)
    {
        //
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param \App\Models\Product $product
     *
     * @return void
     */
    public function deleted(Product $product)
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     *
     * @param \App\Models\Product $product
     *
     * @return void
     */
    public function restored(Product $product)
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     *
     * @param \App\Models\Product $product
     *
     * @return void
     */
    public function forceDeleted(Product $product)
    {
        //
    }
}
