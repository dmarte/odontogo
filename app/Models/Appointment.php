<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    const STATUS_PENDING = 1;
    const STATUS_DELAYED = 2;

    protected $casts = [
        'at'     => 'date',
        'status' => 'int',
    ];

    protected $fillable = [
        'at',
        'title',
        'description',
        'status',
        'document_id',
        'doctor_id',
        'patient_id',
        'team_id',
        'author_user_id',
        'source_id',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'document_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
