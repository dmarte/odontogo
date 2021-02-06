<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public const KIND_AD_SOURCE = 'ads_source';

    public const KIND_GENERAL_CATEGORY = 'category';

    public const KIND_BUDGET_CATEGORY = 'budget_category';
    public const KIND_BUDGET_SUBCATEGORY = 'budget_subcategory';

    public const KINDS = [
        self::KIND_AD_SOURCE,
        self::KIND_DENTAL_INSURANCE,
        self::KIND_DENTAL_CAREER,
        self::KIND_DENTAL_PROCEDURE,
        self::KIND_DENTAL_DIAGNOSIS,
        self::KIND_BUDGET_CATEGORY,
        self::KIND_BUDGET_SUBCATEGORY,
    ];


    protected $casts = ['enabled' => 'boolean'];

    protected $fillable = [
        'name',
        'description',
        'kind',
        'team_id',
        'author_user_id',
        'enabled',
    ];

    public function team() {
        return $this->belongsTo(Team::class);
    }
}
