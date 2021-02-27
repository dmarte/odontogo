<?php

namespace App\Models;

class Vat extends Agreement
{
    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope('kind', fn($query) => $query->where('kind', static::KIND_VAT));
    }
}
