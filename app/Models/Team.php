<?php

namespace App\Models;

use App\Notifications\UserInvitation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

/**
 * Class Team
 *
 * @package App\Models
 * @property-read int                                                        $id
 * @property string                                                          $name
 * @property int                                                             $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\User> $users
 * @property-read \App\Models\User                                           $user
 */
class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'time_zone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            Member::class,
            'team_id',
            'id',
            'id',
            'user_id'
        );
    }

    public function inviteByEmail(string $name, string $email, int $roleId, ?int $authorUserId = null): User
    {
        $passwordRaw = Str::random(6);

        $password = bcrypt($passwordRaw);

        /* @var $user \App\Models\User */
        $user = User::create([
            'name'               => $name,
            'email'              => $email,
            'password'           => $password,
            'invited_by_user_id' => $authorUserId ?? $this->user_id,
            'team_id'            => $this->id,
            'time_zone'          => $this->time_zone,
        ]);

        /* @var $member \App\Models\Member */
        $member = $this->members()->create([
            'user_id'        => $user->id,
            'role_id'        => $roleId,
            'author_user_id' => $authorUserId ?? $this->user_id,
            'status'         => Member::STATUS_INVITED,
            'is_team_owner'  => false,
        ]);

        $user->update(['member_id' => $member->id]);

        $user->notify(new UserInvitation(user: $user, team: $this, member: $member, password: $passwordRaw));

        return $user;
    }
}
