<?php

namespace App\Models;

use App\Casts\UpperCaseCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Class Sequence
 *
 * @package App\Models
 * @property-read int $id
 * @property string $title
 * @property string $subtitle
 * @property Collection $types
 * @property string $prefix
 * @property string $suffix
 * @property int $length
 * @property int $counter
 * @property Carbon|null $expire_at
 * @property int $parent_sequence_id
 * @property int $author_user_id
 * @property int $team_id
 * @property int $maximum
 * @property int $initial_counter
 * @property-read Sequence $parent
 * @property-read User $author
 * @property-read Team $team
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon $deleted_at
 * @property-read string $current_formatted
 * @property-read string $next_formatted
 * @property-read int $next
 * @property-read int $previous
 * @property-read boolean $is_required_increase
 * @property-read int $reminding_numbers
 */
class Sequence extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $hidden = [
        'author_user_id',
        'team_id',
        'parent_sequence_id',
    ];

    protected $appends = [
        'is_required_increase',
        'reminding_numbers',
        'current_formatted',
        'next_formatted',
        'next',
        'previous',
    ];

    protected $casts = [
        'counter' => 'int',
        'length' => 'int',
        'maximum' => 'int',
        'expire_at' => 'date:Y-m-d',
        'deleted_at' => 'date:Y-m-d H:i:s',
        'types' => 'collection',
    ];

    protected $fillable = [
        'title',
        'subtitle',
        'prefix',
        'suffix',
        'length',
        'counter',
        'maximum',
        'expire_at',
        'types',
        'parent_sequence_id',
        'author_user_id',
        'team_id',
        'initial_counter',
    ];

    public static function booted()
    {
        static::creating(function (Sequence $model) {
            if ($model->counter < 1) {
                $model->counter = $model->initial_counter;
            }
        });
        parent::booted();
    }

    public static function format(int $current, ?string $prefix, ?string $suffix, ?int $length): string
    {
        $prefixLength = strlen($prefix);
        $suffixLength = strlen($suffix);
        $length -= ($prefixLength + $suffixLength);
        if ($length < 0) {
            $length = 0;
        }
        return join('', [
            $prefix,
            str_pad($current, $length ?? 0, '0', STR_PAD_LEFT),
            $suffix,
        ]);
    }

    public function increase(): int
    {
        $this->update([
            'counter' => $this->next,
        ]);

        return $this->counter;
    }

    public function add(int $numbers): bool
    {
        return $this->update(['maximum' => $this->maximum + $numbers]);
    }

    public function getCounterAttribute(?int $counter)
    {

        if ($counter < 1) {
            return $this->initial_counter;
        }

        return $counter;
    }

    public function getNextFormattedAttribute(): string
    {
        return self::format(
            $this->next,
            $this->prefix,
            $this->suffix,
            $this->length
        );
    }

    public function getNextAttribute(): int
    {
        if ($this->counter < 1) {
            return $this->initial_counter;
        }

        if ($this->counter >= $this->maximum) {
            return $this->counter;
        }

        return $this->counter + 1;
    }

    public function getPreviousAttribute(): int
    {

        $number = $this->counter - 1;

        if ($number < 1) {
            return 1;
        }

        return $number;
    }

    protected function getIsRequiredIncreaseAttribute(): bool
    {
        return $this->reminding_numbers <= 10;
    }

    protected function getCurrentFormattedAttribute(): string
    {
        return static::format($this->counter, $this->prefix, $this->suffix, $this->length);
    }

    protected function getRemindingNumbersAttribute(): int
    {
        return floor($this->maximum - $this->counter);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_sequence_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
