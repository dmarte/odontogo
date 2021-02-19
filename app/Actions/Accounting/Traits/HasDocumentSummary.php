<?php


namespace App\Actions\Accounting\Traits;

use App\Actions\Accounting\Documents\Head;
use App\Models\Document;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Trait HasDocumentSummary
 * @package App\Actions\Accounting\Traits
 *
 * @property-read bool $is_paid
 */
trait HasDocumentSummary
{
    public static function allowed(string $field): bool
    {
        return in_array($field, array_keys(static::fields()), true);
    }

    public static function fields(): array
    {
        return [
            'exchange_rate' => 'exchange.rate',
            'exchange_currency' => 'exchange.currency',
            'currency' => 'summary.currency',
            'quantity' => 'summary.quantity',
            'price' => 'summary.price',
            'amount' => 'summary.amount',
            'change'=> 'summary.change',
            'discounts' => 'summary.discounts',
            'taxes' => 'summary.taxes',
            'subtotal' => 'summary.subtotal',
            'total' => 'summary.total',
            'amount_paid' => 'summary.paid',
            'balance' => 'summary.balance',
        ];
    }

    public function summarize(): static
    {
        $values = [];

        foreach (array_keys(static::fields()) as $field) {

            $method = static::method(
                field: $field,
                prefix: 'summarized',
                suffix: ''
            );

            if (!method_exists($this, $method) || !$this->isFillable($field)) {
                continue;
            }

            $values[$field] = $this->{$method}();
        }
        $this->fill($values);

        $this->paid = $this->amount_paid >= $this->total;

        return $this;
    }

    public static function method(
        string $field,
        ?string $prefix = 'summary',
        ?string $suffix = 'field'
    ): string {
        return join(
            separator: '',
            array: [
                Str::lower($prefix),
                Str::ucfirst(Str::camel($field)),
                Str::ucfirst($suffix)
            ]
        );
    }

    public function toSummary(): array
    {
        $summary = [];

        foreach (static::fields() as $modelKey => $resourceKey) {

            if (!$this->isFillable($modelKey) || !is_string($resourceKey)) {
                continue;
            }

            Arr::set($summary, $resourceKey, $this->getAttribute($modelKey));
        }


        return $summary;
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }
}
