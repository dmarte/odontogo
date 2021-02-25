<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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

    public function agreement(int $sourceAttributeId) : Agreement {
        $agreement = $this->agreements->filter(function(Agreement $agreement) use($sourceAttributeId) {
                return (is_null($agreement->source_attribute_id) || $sourceAttributeId === (int) $agreement->source_attribute_id);
            })
            ->sortByDesc('source_attribute_id')
            ->first();

        return $agreement ?? new Agreement();
    }

    public function career(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'career_attribute_id');
    }

    public function agreements() : HasMany {
        return $this->hasMany(Agreement::class, 'model_id')->where('model_type', $this->getMorphClass());
    }

    public function transactions() : HasMany {
        return $this->hasMany(Item::class, 'provider_contact_id');
    }
}
