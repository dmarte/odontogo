<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Attribute
{
    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope('team_id', fn($query) => $query->where('team_id', request()->user()->team->id));
        static::addGlobalScope('kind', fn($query) => $query->where('kind', self::KIND_WALLET));
    }
}
