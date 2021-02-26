<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Doctor
 *
 * @package App\Models
 * @property-read \Illuminate\Database\Eloquent\Collection $report
 */
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

    public function agreement(int $sourceAttributeId): Agreement
    {
        $agreement = $this->agreements->filter(function (Agreement $agreement) use ($sourceAttributeId) {
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

    public function agreements(): HasMany
    {
        return $this->hasMany(Agreement::class, 'model_id')->where('model_type', $this->getMorphClass());
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Item::class, 'provider_contact_id');
    }

    public function incomes(): HasMany
    {
        return $this->transactions()->where('data->kind', Document::KIND_PAYMENT_RECEIPT);
    }

    public function expenses(): HasMany
    {
        return $this->transactions()->where('data->kind', Document::KIND_EXPENSE);
    }

    public function report(): HasMany
    {
        return $this
            ->transactions()
            ->addSelect([
                'documents_items.*',
                'agreements.unit_value',
                'agreements.unit_type',
                'agreements.used_after_expenses',
                 'source'=> Source::select('name')->whereColumn('attributes.id', 'agreements.source_attribute_id')
            ])
            ->join('agreements','agreements.model_id', 'documents_items.provider_contact_id')
            ->join('attributes','attributes.id','agreements.source_attribute_id')
            ->where('agreements.model_type', $this->getMorphClass())
            ->whereNotNull('agreements.source_attribute_id')
            ->where(function(Builder $query){
                $query->where('documents_items.data->kind', Document::KIND_EXPENSE)
                    ->orWhere('documents_items.data->kind', Document::KIND_PAYMENT_RECEIPT);
            })
            ->whereHas('receiver', function (Builder $query) {
                $query->whereRaw('`contacts`.`source_attribute_id` = `agreements`.`source_attribute_id`');
            })
            ->orderByDesc('documents_items.data->kind');
    }
}
