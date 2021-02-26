<?php

namespace App\Models;

class BudgetTransaction extends Item
{
    public static function booted()
    {
        parent::booted();
        static::addGlobalScope('data.kind', fn($query) => $query->where('data->kind', Document::KIND_INVOICE_BUDGET));
        static::creating(function (ExpenseTransaction $transaction) {
            $transaction->data = array_merge($transaction->data, [
                'kind' => Document::KIND_INVOICE_BUDGET,
            ]);
        });
    }
}