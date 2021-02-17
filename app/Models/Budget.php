<?php

namespace App\Models;

class Budget extends Document
{
    public function products()
    {
        return $this->hasManyThrough(Product::class, Item::class);
    }
}
