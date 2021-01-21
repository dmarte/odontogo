<?php

namespace App\Models;

use App\Notifications\UserInvitation;
use App\Pivots\TeamUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Ramsey\Collection\Collection;

/**
 * Class Team
 *
 * @package App\Models
 * @property-read int                                                        $id
 * @property string                                                          $name
 * @property int                                                             $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\User> $users
 * @property-read \App\Models\User $user
 */
class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->using(TeamUser::class)
            ->withPivot(['id','scopes','status','token','invited_at'])
            ->withTimestamps()
            ->as('membership');
    }

    public function inviteByEmail(string $name, string $email, array|Collection $scopes = [], ?int $authorUserId = null): User
    {
        $passwordRaw = Str::random(6);

        $password = bcrypt($passwordRaw);

        $id = $this
            ->users()
            ->create(
                [
                    'name'               => $name,
                    'email'              => $email,
                    'password'           => $password,
                    'invited_by_user_id' => $authorUserId ?? $this->user_id,
                ],
                [
                    'scopes'         => $scopes,
                    'author_user_id' => $authorUserId ?? $this->user_id,
                ])
                ->id;

        /* @var $user \App\Models\User */
        $user = $this->users()->wherePivot('user_id', $id)->first();

        $user->notify(new UserInvitation(user: $user, team: $this, membership: $user->membership, password: $passwordRaw));

        return $user;
    }
}
