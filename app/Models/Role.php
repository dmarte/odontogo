<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Role
 *
 * @package App\Models
 * @property-read int $id
 * @property string $name
 * @property int $level
 * @property \Illuminate\Support\Collection $scopes
 * @property int $team_id
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 * @property-read bool $is_administrator
 */
class Role extends Model
{
    use HasFactory;

    protected $casts = [
        'scopes'=> 'array',
        'level'=>'int',
        'team_id'=>'int'
    ];

    protected $fillable = [
        'name',
        'level',
        'scopes',
        'team_id'
    ];

    public function team() : BelongsTo {
        return $this->belongsTo(Team::class);
    }

    protected function getIsAdministratorAttribute() {
        return $this->level < Member::LEVEL_MODERATOR;
    }
}
