<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Document
{
    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope('team', fn(Builder $query) => $query->where('team_id', request()->user()->team->id));
        static::addGlobalScope('kind', fn(Builder $query) => $query->where('kind', static::KIND_PAYMENT_RECEIPT));

        static::creating(function(Receipt $receipt){
            if (is_null($receipt->paid_by_contact_id)) {
                $receipt->paid_by_contact_id = $receipt->receiver_contact_id;
            }
        });

    }

}
