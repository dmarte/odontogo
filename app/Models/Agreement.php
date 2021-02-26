<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agreement extends Model
{
    use HasFactory;

    protected $casts = ['unit_value' => 'float', 'source_attribute_id' => 'int'];
    protected $fillable = [
        'title',
        'model_type',
        'model_id',
        'source_attribute_id',
        'insurance_attribute_id',
        'unit_value',
        'unit_type',
        'used_after_expenses',
    ];

    protected static function booted()
    {
        parent::booted();
        static::creating(function(Agreement $agreement){
            $agreement->title = "{$agreement->model->name} - {$agreement->unit_representation}";
        });
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'source_attribute_id');
    }

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'insurance_attribute_id');
    }

    protected function getUnitRepresentationAttribute()
    {
        if ($this->unit_type === 'percent') {
            return "{$this->unit_value}%";
        }

        return number_format($this->unit_value * -1).' (' . __('Agreement fix') . ')';
    }
}
