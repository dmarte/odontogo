<?php

namespace App\Models;

use App\Pivots\TeamUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Nova\Actions\Actionable;

/**
 * Class User
 *
 * @package App\Models
 * @property-read int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $invited_by_user_id
 * @property-read TeamUser $membership
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
        'invited_by_user_id'
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

    public function teams() {
        return $this
            ->belongsToMany(Team::class)
            ->withTimestamps()
            ->using(TeamUser::class)
            ->as('membership');
    }
}
