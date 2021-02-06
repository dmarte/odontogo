<?php


namespace App\Models;

use App\Notifications\UserWelcome;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class TeamUser
 *
 * @package App\Models
 * @property-read int                       $id
 * @property int                            $author_user_id
 * @property int                            $team_id
 * @property int                            $user_id
 * @property \Illuminate\Support\Collection $scopes
 * @property string                         $status
 * @property string                         $token
 * @property bool                           $is_team_owner
 * @property \Carbon\Carbon                 $invited_at
 * @property-read \Carbon\Carbon            $created_at
 * @property-read \Carbon\Carbon            $updated_at
 * @property-read User                      $user
 * @property-read Team                      $team
 * @property-read \App\Models\Role          $role
 */
class Member extends Model
{
    const LEVEL_ADMINISTRATOR = 0;
    const LEVEL_MODERATOR = 1;
    const LEVEL_MANAGER = 2;
    const LEVEL_EMPLOYEE = 3;
    const LEVEL_USER = 4;
    const LEVEL_GUEST = 5;

    const SCOPE_MODIFY_TEAM = 'modify_team';
    const SCOPE_INVITE_MEMBER = 'invite_member';
    const SCOPE_CONTACTS_ADD = 'add_contacts';
    const SCOPE_CONTACTS_DELETE = 'delete_contacts';
    const SCOPE_CONTACTS_MODIFY = 'modify_contacts';
    const SCOPE_CONTACTS_MODIFY_CREDIT = 'modify_credit';
    const SCOPE_ATTRIBUTES_ADD = 'add_attributes';
    const SCOPE_ATTRIBUTES_MODIFY = 'modify_attributes';
    const SCOPE_ATTRIBUTES_DELETE = 'delete_attributes';

    const LEVELS = [
        self::LEVEL_ADMINISTRATOR,
        self::LEVEL_MODERATOR,
        self::LEVEL_MANAGER,
        self::LEVEL_EMPLOYEE,
        self::LEVEL_USER,
        self::LEVEL_GUEST,
    ];

    const SCOPES = [
        self::SCOPE_MODIFY_TEAM,
        self::SCOPE_CONTACTS_ADD,
        self::SCOPE_CONTACTS_DELETE,
        self::SCOPE_CONTACTS_MODIFY,
        self::SCOPE_INVITE_MEMBER,
        self::SCOPE_ATTRIBUTES_ADD,
        self::SCOPE_ATTRIBUTES_DELETE,
        self::SCOPE_ATTRIBUTES_MODIFY,
        self::SCOPE_CONTACTS_MODIFY_CREDIT
    ];

    const STATUSES = [
        self::STATUS_INVITED,
        self::STATUS_JOINED,
    ];

    const STATUS_INVITED = 'invited';
    const STATUS_JOINED = 'joined';

    protected $casts = [
        'scopes'        => 'collection',
        'user_id'=> 'int',
        'is_team_owner' => 'boolean',
        'invited_at'    => 'date',
        'joined_at'     => 'date',
    ];
    protected $attributes = [
        'status' => self::STATUS_INVITED,
    ];
    protected $fillable = [
        'status',
        'token',
        'invited_at',
        'joined_at',
        'author_user_id',
        'is_team_owner',
        'role_id',
        'team_id',
        'user_id',
        'contact_id',
    ];

    public static function scopesAsOptions(): Collection
    {
        return collect(Member::SCOPES)
            ->mapWithKeys(function ($scope) {
                return [$scope => __("scope.{$scope}")];
            });
    }

    public static function statusesAsOptions(): Collection
    {
        return collect(Member::STATUSES)
            ->mapWithKeys(function ($status) {
                return [$status => __(Str::ucfirst($status))];
            });
    }

    public function contact() : BelongsTo {
        return $this->belongsTo(Contact::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function activate(): void
    {
        $this->update([
            'status'    => self::STATUS_JOINED,
            'joined_at' => now(),
        ]);

        $this->user->notify(new UserWelcome($this->user, $this, $this->team));
    }
}
