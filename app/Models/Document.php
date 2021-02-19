<?php

namespace App\Models;

use App\Actions\Accounting\Documents\Head;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;

class Document extends Head
{
    use HasFactory;
    use HasFlexible;

    public function products()
    {
        return $this->hasManyThrough(Product::class, Item::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'document_id');
    }
}
