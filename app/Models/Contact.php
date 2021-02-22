<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

/**
 * Class Contact
 *
 * @package App\Models
 * @property-read int $id
 * @property string $avatar_path
 * @property string $avatar_disk
 * @property int $avatar_size
 * @property string $code
 * @property int $counter
 * @property string $kind
 * @property string $name
 * @property string $tax_payer_name
 * @property string $tax_payer_number
 * @property string $tax_payer_type
 * @property string $identification_number
 * @property int $insurance_attribute_id
 * @property int $source_attribute_id
 * @property int $sequence_id
 * @property string $title
 * @property string $company
 * @property string $phone_primary
 * @property string $phone_secondary
 * @property string $email_primary
 * @property string $email_secondary
 * @property string $notes
 * @property string $gender
 * @property string $country_code
 * @property string $currency_code
 * @property \Carbon\Carbon|null $birthday
 * @property \Carbon\Carbon $registered_at
 * @property string $address_line_1
 * @property string $address_line_2
 * @property string $city_name
 * @property string $postal_code
 * @property float $latitude
 * @property float $longitude
 * @property int $team_id
 * @property int $author_user_id
 * @property int $updated_by_user_id
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 * @property-read \Carbon\Carbon|null $deleted_at
 */
class Contact extends Model
{
    use HasFactory, SoftDeletes;
    use Notifiable;

    public const GENDER_NONE = 'none';
    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';

    public const KIND_CONTACT = 'CO';
    public const KIND_DOCTOR = 'DR';
    public const KIND_PROVIDER = 'PR';
    public const KIND_PATIENT = 'PA';

    protected $table = 'contacts';
    protected $casts = [
        'registered_at' => 'date',
        'deleted_at'    => 'date',
        'birthday'      => 'date',
        'counter'       => 'int',
        'emails'        => 'array',
        'phones'        => 'array',
        'avatar_size'   => 'int',
        'latitude'      => 'float',
        'longitude'     => 'float',
    ];

    protected $fillable = [
        'avatar_path',
        'avatar_disk',
        'avatar_size',
        'code',
        'counter',
        'kind',
        'name',
        'sequence_id',
        'tax_payer_type',
        'tax_payer_name',
        'tax_payer_number',
        'identification_number',
        'insurance_number',
        'responsible_contact_id',
        'insurance_attribute_id',
        'source_attribute_id',
        'category_attribute_id',
        'subcategory_attribute_id',
        'career_attribute_id',
        'title',
        'company',
        'phone_primary',
        'phone_secondary',
        'email_primary',
        'email_secondary',
        'notes',
        'gender',
        'country_code',
        'currency_code',
        'birthday',
        'registered_at',
        'address_line_1',
        'address_line_2',
        'city_name',
        'postal_code',
        'latitude',
        'longitude',
        'team_id',
        'user_id',
        'author_user_id',
        'updated_by_user_id',
        'credit_value',
        'credit_days',
    ];

    public function routeNotificationForMail(Notification $notifiable)
    {
        return [$this->email_primary ?? $this->email_secondary => $this->name];
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'insurance_attribute_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'category_attribute_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'subcategory_attribute_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'source_attribute_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responsible() : BelongsTo {
        return $this->belongsTo(Contact::class, 'responsible_contact_id');
    }
}
