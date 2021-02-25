<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Provider extends Contact
{
    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope('team', fn(Builder $query) => $query->where('team_id', request()->user()->team->id));
        static::addGlobalScope('kind', fn(Builder $query) => $query->where('kind', static::KIND_PROVIDER));
    }
}
