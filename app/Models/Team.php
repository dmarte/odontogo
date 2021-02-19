<?php

namespace App\Models;

use App\Notifications\UserInvitation;
use Illuminate\Database\Eloquent\Builder;
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
 * @property string                                                          $country
 * @property string                                                          $currency
 * @property string $vat
 * @property string $avatar_path
 * @property string $avatar_disk
 * @property int $avatar_size
 * @property string $phone_primary
 * @property string $phone_secondary
 * @property string $address_line_1
 * @property string $email
 * @property string $locale
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\User> $users
 * @property-read \App\Models\User                                           $user
 */
class Team extends Model
{
    use HasFactory;

    protected $attributes = [
        'country'=> 'DO',
        'currency'=> 'DOP',
        'locale'=> 'es',
        'time_zone'=>'America/Santo_Domingo'
    ];
    protected $fillable = [
        'name',
        'vat',
        'avatar_path',
        'avatar_disk',
        'avatar_size',
        'phone_primary',
        'phone_secondary',
        'address_line_1',
        'email',
        'user_id',
        'country',
        'currency',
        'locale',
        'time_zone',
        'address_line_2',
        'primary_color'
    ];

    public function contacts() : HasMany {
        return $this->hasMany(Contact::class);
    }

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

    public function catalog(): HasMany {
        return $this
            ->hasMany(Attribute::class)
            ->where('kind', Attribute::KIND_CATALOG_ACCOUNTING)
            ->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('name');
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(Sequence::class);
    }

    public function budgetSequence() {
        return $this
            ->hasOne(Sequence::class)
            ->where('types->' . Document::KIND_INVOICE_BUDGET, true)
            ->withDefault();
    }

    public function sequencesForInvoices() : HasMany {
        return $this
            ->sequencesNotExpired()
            ->where(function(Builder $builder) {
                $builder
                    ->where('types->'. Document::KIND_CREDIT_INVOICE, true)
                    ->orWhere('types->'.Document::KIND_CASH_BILL, true);
            });
    }

    public function sequencesNotExpired(): HasMany
    {
        return $this
            ->sequences()
            ->where(function (Builder $query) {

                $query
                    ->whereNull('expire_at')
                    ->orWhereRaw("expire_at > DATE(NOW())")
                    ->orWhereRaw("expire_at = DATE(NOW())");
            });
    }

    public function diagnosis(): HasMany
    {
        return $this
            ->attributes()
            ->where('kind', Attribute::KIND_DENTAL_DIAGNOSIS);
    }

    public function insurances(): HasMany
    {
        return $this
            ->attributes()
            ->where('kind', Attribute::KIND_DENTAL_INSURANCE);
    }

    public function careers(): HasMany
    {
        return $this
            ->attributes()
            ->where('kind', Attribute::KIND_DENTAL_CAREER);
    }

    public function sources(): HasMany
    {
        return $this
            ->attributes()
            ->where('kind', Attribute::KIND_AD_SOURCE);
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
