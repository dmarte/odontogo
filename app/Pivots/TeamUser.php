<?php


namespace App\Pivots;

use App\Models\Team;
use App\Models\User;
use App\Notifications\UserWelcome;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class TeamUser
 *
 * @package App\Pivots
 * @property-read int                       $id
 * @property int                            $author_user_id
 * @property int                            $team_id
 * @property int                            $user_id
 * @property \Illuminate\Support\Collection $scopes
 * @property string                         $status
 * @property string                         $token
 * @property \Carbon\Carbon                 $invited_at
 * @property-read \Carbon\Carbon            $created_at
 * @property-read \Carbon\Carbon            $updated_at
 * @property-read User                      $user
 * @property-read Team                      $team
 */
class TeamUser extends Pivot
{
    const SCOPE_ADMIN = 'admin';
    const SCOPE_MODIFY_TEAM = 'modify_team';
    const SCOPE_CONTACTS_ADD = 'add_contacts';
    const SCOPE_CONTACTS_DELETE = 'delete_contacts';
    const SCOPE_CONTACTS_MODIFY = 'modify_contacts';

    const SCOPES = [
        self::SCOPE_ADMIN,
        self::SCOPE_MODIFY_TEAM,
        self::SCOPE_CONTACTS_ADD,
        self::SCOPE_CONTACTS_DELETE,
        self::SCOPE_CONTACTS_MODIFY,
    ];

    const STATUSES = [
        self::STATUS_INVITED,
        self::STATUS_JOINED
    ];

    const STATUS_INVITED = 'invited';
    const STATUS_JOINED = 'joined';

    public $incrementing = true;
    protected $casts = [
        'scopes'     => 'collection',
        'invited_at' => 'date',
        'joined_at'  => 'date',
    ];
    protected $attributes = [
        'status' => self::STATUS_INVITED,
    ];
    protected $fillable = [
        'scopes',
        'status',
        'token',
        'invited_at',
        'joined_at',
        'author_user_id',
        'team_id',
        'user_id',
    ];

    public static function scopesAsOptions(): Collection
    {
        return collect(TeamUser::SCOPES)
            ->mapWithKeys(function ($scope) {
                return [$scope => __("scope.{$scope}")];
            });
    }

    public static function statusesAsOptions(): Collection
    {
        return collect(TeamUser::STATUSES)
            ->mapWithKeys(function ($status) {
                return [$status => __(Str::ucfirst($status))];
            });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function activate()
    {
        $this->update([
            'status'    => self::STATUS_JOINED,
            'joined_at' => now(),
        ]);

        $this->user->notify(new UserWelcome($this->user, $this, $this->team));
    }
}
