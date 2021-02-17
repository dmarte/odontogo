<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Doctor extends Contact
{
    use HasFactory;

    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope(function (Builder $query) {
            $query->where('kind', self::KIND_DOCTOR);
        });
    }

    public function career(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'career_attribute_id');
    }
}
