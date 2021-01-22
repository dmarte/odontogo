<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use HasFactory, SoftDeletes;

    const KIND_ADS_SOURCE = 'ads_source';
    const KIND_CAREER = 'career';

    protected $casts = ['enabled' => 'boolean'];

    protected $fillable = [
        'name',
        'description',
        'kind',
        'team_id',
        'enabled',
    ];

    public function team() {
        return $this->belongsTo(Team::class);
    }
}
