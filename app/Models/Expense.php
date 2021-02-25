<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Document
{
    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope('team', fn(Builder $query) => $query->where('team_id', request()->user()->team->id));
        static::addGlobalScope('kind', fn(Builder $query) => $query->where('kind', static::KIND_EXPENSE));
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseTransaction::class);
    }
}
