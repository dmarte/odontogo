<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Attribute
 *
 * @package App\Models
 * @property-read int $id
 * @property string $name
 * @property string $description
 * @property string $kind
 * @property int $team_id
 * @property bool $enabled
 * @proeprty string $code
 * @property int $parent_id
 * @property int $author_user_id
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 * @property-read \Carbon\Carbon $deleted_at
 */
class Attribute extends Model
{
    use HasFactory, SoftDeletes;

    public const KIND_DENTAL_CAREER = 'career';
    public const KIND_DENTAL_INSURANCE = 'dental_insurance';
    public const KIND_DENTAL_PROCEDURE = 'dental_procedure';
    public const KIND_DENTAL_DIAGNOSIS = 'dental_diagnosis';
    public const KIND_WALLET = 'wallet';
    public const KIND_GENERAL_TAX_PAYER_TYPE='tax_payer_type';
    public const KIND_AD_SOURCE = 'ads_source';

    public const KIND_GENERAL_CATEGORY = 'category';

    public const KIND_CATALOG_ACCOUNTING = 'catalog';
    public const KIND_BUDGET = 'budget';

    public const KIND_COUNTRY = 'country';
    public const KIND_CITY = 'city';
    public const KIND_STATE = 'state';

    public const KINDS = [
        self::KIND_AD_SOURCE,
        self::KIND_DENTAL_INSURANCE,
        self::KIND_DENTAL_CAREER,
        self::KIND_DENTAL_PROCEDURE,
        self::KIND_DENTAL_DIAGNOSIS,
        self::KIND_CATALOG_ACCOUNTING,
        self::KIND_BUDGET,
    ];

    protected $table = 'attributes';

    protected $casts = [
        'enabled'        => 'boolean',
        'system_default' => 'boolean',
        'amount_credit'  => 'float',
        'amount_debit'   => 'float',
        'data'           => 'array',
    ];

    protected $fillable = [
        'name',
        'description',
        'kind',
        'team_id',
        'author_user_id',
        'enabled',
        'data',
        'parent_id',
        'code',
        'system_default',
        'amount_credit',
        'amount_debit'
    ];

    protected static function booted()
    {
        parent::booted();
        static::creating(function (Attribute $attribute) {
            if (!$attribute->code) {
                $attribute->code = static::where('team_id', $attribute->team_id)
                        ->where('kind', $attribute->kind)
                        ->count() + 1;
            }
        });
    }

    public function items(string $column = 'wallet_attribute_id') : HasMany {
        return $this->hasMany(Item::class, $column);
    }

    public function summarize($relationColumn = 'wallet_attribute_id') {
        $this->update([
            'amount_credit'=> $this
                ->items($relationColumn)
                ->where('data->kind', Document::KIND_PAYMENT_RECEIPT)
                ->sum('amount_paid')
        ]);
    }

    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
