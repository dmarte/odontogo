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

    public function title(): string
    {
        return join(' ', array_filter([
            $this->code,
            $this->format('total'),
            $this->id && $this->emitted_at ? "({$this->emitted_at?->locale(app()->getLocale())->toFormattedDateString()})" : null
        ]));
    }

    public function format(string $field): string
    {
        if (!$this->allowed($field)) {
            return 0;
        }

        return (new NumberFormatter('en-US', NumberFormatter::CURRENCY))
            ->formatCurrency($this->{$field}, $this->currency);
    }
}
