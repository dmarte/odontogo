<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Contact
{
    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope(function(Builder $query) {
            $query->where('kind', self::KIND_PATIENT );
        });
    }

    public function payments() : HasMany {
        return $this
            ->hasMany(Item::class, 'receiver_contact_id')
            ->where('data->kind', Document::KIND_PAYMENT_RECEIPT)
            ;
    }
}
