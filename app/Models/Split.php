<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Split extends Agreement
{
    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope('kind', fn($query) => $query->where('kind', static::KIND_SPLIT));
    }
}
