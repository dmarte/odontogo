<?php

namespace App\Actions\Accounting\Documents;

use Illuminate\Support\Arr;

class ChildData
{
    /**
     * ChildData constructor.
     * @param  Aggregation|null  $discount
     * @param  float|int  $original_price
     * @param  array  $taxes
     */
    public function __construct(
    private Aggregation|null $discount = null,
    private float $original_price = 0,
    private array $taxes = []
    ) {
        if (is_null($this->discount)) {
            $this->discount = Aggregation::discount(['value' => 0]);
        }
    }

    public static function build(?array $data = null)
    {

        if (is_null($data)) {
            return new static();
        }

        $discount = Arr::get($data, 'discount');

        $taxes = array_map(function (Aggregation|array|null $tax) {

            if (is_array($tax)) {
                return Aggregation::tax($tax);
            }

            return $tax;
        }, array_filter((array) Arr::get($data, 'taxes', [])));


        if (is_array($discount)) {
            $discount = Aggregation::discount($discount);
        }

        $entity = new static(
            discount: $discount,
            taxes: $taxes,
            original_price: (float) Arr::get($data, 'original_price', 0)
        );

        return $entity;
    }

    public function getDiscountRate(float $amount, bool $absolute = false): float
    {
        return $this->discount->rate($amount, $absolute);
    }

    public function getTotalTaxes(float $amount, bool $absolute = false): float
    {
        return array_reduce($this->taxes, function (int $aggregated, Aggregation $item) use ($amount, $absolute) {
            return $item->rate($amount, $absolute) + $aggregated;
        }, 0);
    }

    #[JetBrains\PhpStorm\ArrayShape(['discount' => "array", 'original_price' => "float|int", 'taxes' => "mixed"])]
    public function toArray(): array
    {
        return [
            'discount' => $this->discount?->toArray(),
            'original_price' => $this->original_price,
            'taxes' => array_map(
                function (Aggregation|array $item) {
                    return is_array($item) ? $item : $item->toArray();
                },
                $this->taxes
            ),
        ];
    }
}
