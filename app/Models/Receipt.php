<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class Receipt extends Document
{
    protected static function booted()
    {
        parent::booted();

        $team = request()->user()->team;

        static::addGlobalScope('team', fn(Builder $query) => $query->where('team_id', $team->id));
        static::addGlobalScope('kind', fn(Builder $query) => $query->where('kind', static::KIND_PAYMENT_RECEIPT));

        static::creating(function (Receipt $receipt) use($team) {

            $receipt->calculateReceiptData();

            if (is_null($receipt->paid_by_contact_id)) {
                $receipt->paid_by_contact_id = $receipt->receiver_contact_id;
            }

            if (is_null($receipt->wallet_attribute_id)) {
                $receipt->wallet_attribute_id = $team->wallet_attribute_id;
            }
        });

        static::updating(function(Receipt $receipt) {
            $receipt->calculateReceiptData();
        });
    }

    public function calculateReceiptData() {
        $cash = (float) Arr::get($this->data, 'amount.cash', 0);
        $creditCard = (float) Arr::get($this->data, 'amount.credit_card', 0);
        $creditNote = (float) Arr::get($this->data, 'amount.credit_note', 0);
        $bankTransfer = (float) Arr::get($this->data, 'amount.bank_transfer', 0);
        $data = $this->data;
        $data['summary']['total'] = $cash + $creditCard + $creditNote + $bankTransfer;
        $data['summary']['available'] = $data['summary']['total'] - $this->total;
        $data['amount']['cash'] = $cash;
        $data['amount']['credit_card'] = $creditCard;
        $data['amount']['credit_note'] = $creditNote;
        $data['amount']['bank_transfer'] = $bankTransfer;
        $this->amount_paid = $data['summary']['total'];
        $this->data = $data;
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceiptTransaction::class);
    }
}
