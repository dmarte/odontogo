<?php

namespace App\Models;

class ReceiptTransaction extends Item
{
    public static function booted()
    {
        parent::booted();

        static::addGlobalScope('data.kind', fn($query) => $query->where('data->kind', Document::KIND_PAYMENT_RECEIPT));

        static::creating(function (ReceiptTransaction $transaction) {


        });
    }
}
