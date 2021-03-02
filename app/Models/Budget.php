<?php

namespace App\Models;

use App\Printer\BudgetPrinter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Budget
 *
 * @package App\Models
 * @property-read BudgetPrinter $pdf
 */
class Budget extends Document
{
    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope('team', fn(Builder $query) => $query->where('team_id', request()->user()->team->id));
        static::addGlobalScope('kind', fn(Builder $query) => $query->where('kind', static::KIND_INVOICE_BUDGET));

    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetTransaction::class);
    }

    public function getPdfAttribute() {
        return new BudgetPrinter($this);
    }
}
