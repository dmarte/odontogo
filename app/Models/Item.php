<?php

namespace App\Models;

use App\Actions\Accounting\Documents\Child;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;

/**
 * Class Item
 *
 * @package App\Models
 * @property-read \App\Models\Document $document
 * @property-read \App\Models\Wallet $wallet
 */
class Item extends Child
{
    use HasFlexible;

    public function receipt() {
        return $this->belongsTo(Receipt::class, 'document_id');
    }

    public function doctor() {
        return $this->belongsTo(Doctor::class, 'provider_contact_id');
    }

    public function patient() {
        return $this->belongsTo(Patient::class, 'receiver_contact_id');
    }
}
