<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Patient extends Contact
{
    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope(function(Builder $query) {
            $query->where('kind', self::KIND_PATIENT );
        });
    }
}
