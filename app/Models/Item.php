<?php

namespace App\Models;

use App\Actions\Accounting\Documents\Child;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;

/**
 * Class Item
 *
 * @package App\Models
 * @property-read \App\Models\Document $document
 */
class Item extends Child
{
    use HasFlexible;

    public function document() : BelongsTo {
        return $this->belongsTo(Document::class);
    }

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
