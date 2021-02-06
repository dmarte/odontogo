<?php

namespace App\Models;

use App\Actions\Accounting\Documents\Head;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;

class Document extends Head
{
    use HasFactory;
    use HasFlexible;

    public function services() {
        return $this->hasManyThrough(Product::class,Item::class);
    }
}
