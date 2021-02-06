<?php


namespace App\Actions\Accounting\Interfaces;


interface Summarizable
{
    /**
     * Determine if a given field is allowed to be summarized.
     *
     * NOTE:
     * This not refers to the model attributes but the fields
     * in the current instance that represent the sumarizable
     * values.
     *
     * @param  string  $field
     * @return bool
     */
    public static function allowed(string $field): bool;

    /**
     * This method determine what is the "action" or the method
     * that will trigger the value of a given "allowed sumarizable field."
     *
     * @param  string  $field
     * @param  string|null  $prefix
     * @param  string|null  $suffix
     * @return string
     */
    public static function method(string $field, ?string $prefix, ?string $suffix): string;

    /**
     * This method should return the fields
     * allowed to a given instance type.
     *
     * @return array<string>
     */
    public static function fields(): array;

    /**
     * Get the resume total for a given field.
     *
     * @param  string  $field
     * @return float|int
     */
    public function summary(string $field): float|int;

    public function summarize(): static;

    public function summarizedQuantity(): float|int;

    public function summarizedPrice(): float|int;

    public function summarizedDiscounts(): float|int;

    public function summarizedTaxes(): float|int;

    public function summarizedAmount(): float|int;

    public function summarizedSubtotal(): float|int;

    public function summarizedTotal(): float|int;

    public function summarizedBalance(): float|int;

    public function summarizedAmountPaid(): float|int;
}
