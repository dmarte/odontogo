<?php


namespace App\Actions\Accounting\Interfaces;


use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Interface DocumentTransformable
 * Used to determine the methods a document formated
 * should have to "transform" a given document.
 *
 * @package App\Actions\Accounting\Interfaces
 */
interface Transformable
{
    public static function buildFromArrayModel(array $model): static;

    public static function buildFromArrayResource(array $resource): static;

    /**
     * This method converts a given Model or JsonResource
     * to the resource array structure.
     *
     * @param  Model  $model
     * @return array
     */
    public static function toResourceArray(Model $model): array;

    /**
     * Converts a resource array format
     * to a model array format.
     *
     * @param  array  $resource
     * @return array
     */
    public static function toModelArray(array $resource): array;

    /**
     * Get an array version of the given sumarizable object.
     *
     * @return array
     */
    #[ArrayShape([
        'summary' => [
            'quantity' => "float",
            'price' => "float",
            'amount' => "float",
            'taxes' => "float",
            'discounts' => "float",
            'subtotal' => "float",
            'total' => "float",
            'paid' => "float",
            'balance' => "float",
            'currency' => "string",
        ]
    ])]
    public function toSummary(): array;
}
