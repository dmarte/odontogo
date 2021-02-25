<?php

namespace App\Models;

class ExpenseTransaction extends Item
{
    public static function booted()
    {
        parent::booted();
        static::addGlobalScope('data.kind', fn($query) => $query->where('data->kind', Document::KIND_EXPENSE));

        static::creating(function(ExpenseTransaction $transaction){
            $transaction->emitted_at = $transaction->document->emitted_at;
            $transaction->data = array_merge((array) $transaction->data, [
                'kind'=>Document::KIND_EXPENSE
            ]);
        });
    }
}
