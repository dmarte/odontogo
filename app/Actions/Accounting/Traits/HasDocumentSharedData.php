<?php


namespace App\Actions\Accounting\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use NumberFormatter;

/**
 * Trait HasDocumentSharedData
 * @package App\Actions\Accounting\Traits
 *
 * @property string $currency
 * @property int $quantity
 * @property float $price
 * @property float $amount
 * @property float $taxes
 * @property float $discounts
 * @property float $subtotal
 * @property float $total
 * @property float $balance
 * @property float $amount_paid
 * @property float $change
 * @property bool $paid
 * @property bool $completed
 * @property bool $cancelled
 * @property bool $verified
 * @property Carbon $emitted_at
 * @property Carbon $expire_at
 * @property Carbon $paid_at
 * @property Carbon $completed_at
 * @property Carbon $cancelled_at
 * @property Carbon $verified_at
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon $deleted_at
 */
trait HasDocumentSharedData
{
    use SoftDeletes;
    use HasDocumentRelations;
    use HasDocumentSummary;

    public static function buildFromArrayResource(array $resource): static
    {
        return static::buildFromArrayModel(static::toModelArray($resource));
    }

    public static function buildFromArrayModel(array $model): static
    {
        return (new static($model))->summarize();
    }

    public function getCasts()
    {
        return array_merge(
            parent::getCasts(),
            [
                'quantity' => 'int',
                'price' => 'float',
                'amount' => 'float',
                'discounts' => 'float',
                'taxes' => 'float',
                'subtotal' => 'float',
                'total' => 'float',
                'amount_paid' => 'float',
                'balance' => 'float',
                'change'=> 'float',
                'paid' => 'boolean',
                'completed' => 'boolean',
                'cancelled' => 'boolean',
                'verified' => 'boolean',
                'emitted_at' => 'date:Y-m-d',
                'expire_at' => 'date:Y-m-d',
                'paid_at' => 'date:Y-m-d',
                'completed_at' => 'date:Y-m-d',
                'verified_at' => 'date:Y-m-d',
                'deleted_at' => 'date:Y-m-d',
                'cancelled_at' => 'date:Y-m-d',
            ]
        );
    }

    public function getFillable()
    {
        return array_merge(
            parent::getFillable(),
            [
                'title',
                'description',
                'currency',
                'quantity',
                'price',
                'amount',
                'amount_paid',
                'price',
                'taxes',
                'discounts',
                'subtotal',
                'total',
                'balance',
                'change',
                'paid',
                'completed',
                'cancelled',
                'verified',
                'expire_at',
                'emitted_at',
                'paid_at',
                'completed_at',
                'cancelled_at',
                'verified_at',
                'team_id',
                'category_attribute_id',
                'subcategory_attribute_id',
                'provider_contact_id',
                'receiver_contact_id',
                'paid_by_contact_id',
                'author_user_id',
                'completed_by_user_id',
                'cancelled_by_user_id',
                'updated_by_user_id',
                'deleted_by_user_id',
            ]
        );
    }

    public function title(): string
    {
        return join(' ', array_filter([
            $this->code,
            $this->currency,
            $this->format('total'),
            $this->id && $this->emitted_at ? "({$this->emitted_at?->locale(app()->getLocale())->toFormattedDateString()})" : null
        ]));
    }

    public function format(string $field): string
    {
        if (!$this->allowed($field)) {
            return 0;
        }

        return (new NumberFormatter($this->locale, NumberFormatter::CURRENCY))
            ->formatCurrency($this->{$field}, $this->currency);
    }
}
