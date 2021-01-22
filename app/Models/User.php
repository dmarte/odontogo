<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Nova\Actions\Actionable;

/**
 * Class User
 *
 * @package App\Models
 * @property-read int                                                        $id
 * @property string                                                          $name
 * @property string                                                          $email
 * @property string                                                          $password
 * @property string                                                          $locale
 * @property int $member_id
 * @property string                                                          $time_zone
 * @property int                                                             $invited_by_user_id
 * @property int                                                             $team_id
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Team> $teams
 * @property-read \App\Models\Member                                         $member
 * @property-read \Illuminate\Database\Eloquent\Collection                   $memberships
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, Actionable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'time_zone',
        'invited_by_user_id',
        'team_id',
        'member_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class)->withDefault();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)->withDefault();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Member::class)->with('role');
    }

    public function teams(): HasManyThrough
    {
        return $this->hasManyThrough(
            Team::class,
            Member::class,
            'user_id',
            'id',
            'id',
            'team_id'
        );
    }
}
