<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Product
 *
 * @package App\Models
 * @property-read int    $id
 * @property string      $name
 * @property float       $price
 * @property string      $currency
 * @property int         $counter
 * @property string      $prefix
 * @property string      $code
 * @property int         $team_id
 * @property int         $parent_id
 * @property int         $insurance_attribute_id
 * @property int         $career_attribute_id
 * @property int         $author_user_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon $deleted_at
 */
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    const PREFIX_SERVICE = 'SE';
    const PREFIX_PRODUCT = 'PR';
    const PREFIX_MATERIAL = 'MA';

    protected $attributes = [
        'prefix' => self::PREFIX_SERVICE,
    ];

    protected $casts = [
        'counter'    => 'int',
        'price'      => 'float',
        'deleted_at' => 'date:Y-m-d',
    ];

    protected $fillable = [
        'name',
        'counter',
        'prefix',
        'code',
        'price',
        'currency',
        'team_id',
        'insurance_attribute_id',
        'career_attribute_id',
        'author_user_id',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'insurance_attribute_id')->withDefault();
    }

    public function career()
    {
        return $this->belongsTo(Attribute::class, 'career_attribute_id')->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'author_user_id')->withDefault();
    }
}
