<?php


namespace App\Actions\Accounting\Documents;

use Exception;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class Aggregation
 * This class let you handle single Tax, Discount or Charge item.
 *
 * @package App\Actions\Accounting
 */
class Aggregation implements Arrayable
{
    public const METHOD_CASH = 'cash';
    public const METHOD_CREDIT_CARD = 'credit_card';
    public const METHOD_PAYMENT_METHOD_CHECK = 'check';

    public const VALUE_TYPE_FIX = 'fix';
    public const VALUE_TYPE_PERCENT = 'percent';
    public const VALUE_TYPE_PAYMENT = 'payment';

    public const OPERATION_TYPE_ADD = '+';
    public const OPERATION_TYPE_SUBTRACT = '-';

    public const VALUE_TYPES = [
        self::VALUE_TYPE_FIX,
        self::VALUE_TYPE_PERCENT,
        self::VALUE_TYPE_PAYMENT
    ];

    public const OPERATIONS = [
        self::OPERATION_TYPE_ADD,
        self::OPERATION_TYPE_SUBTRACT,
    ];

    public const PAYMENT_METHODS = [
        self::METHOD_CASH,
        self::METHOD_CREDIT_CARD,
        self::METHOD_PAYMENT_METHOD_CHECK,
    ];

    public function __construct(
        protected float $_value = 0,
        protected ?string $_label = null,
        protected ?string $_confirmation = null,
        protected string $_method = self::METHOD_CASH,
        protected string $_operation = self::OPERATION_TYPE_ADD,
        protected string $_type = self::VALUE_TYPE_PERCENT
    ) {
    }

    public static function cashPayment(float $amount): static
{
        return static::build([
            'value' => $amount,
            'label' => self::METHOD_CASH,
            'confirmation' => null,
            'method' => self::METHOD_CASH,
            'operation' => self::OPERATION_TYPE_SUBTRACT,
            'type'=> self::VALUE_TYPE_PAYMENT
        ])
            ->useAsSubtraction();
    }

    public static function creditCardPayment(float $amount, ?string $confirmation = null): static
{
        return static::build([
            'value' => $amount,
            'label' => self::METHOD_CREDIT_CARD,
            'confirmation' => $confirmation,
            'method' => self::METHOD_CREDIT_CARD,
            'operation' => self::OPERATION_TYPE_SUBTRACT,
            'type'=> self::VALUE_TYPE_PAYMENT
        ])
            ->useAsSubtraction();
    }

    public static function checkPayment(float $amount, ?string $confirmation = null): static
{
        return static::build([
            'value' => $amount,
            'label' => self::METHOD_PAYMENT_METHOD_CHECK,
            'confirmation' => $confirmation,
            'method' => self::METHOD_PAYMENT_METHOD_CHECK,
            'operation' => self::OPERATION_TYPE_SUBTRACT,
            'type'=> self::VALUE_TYPE_PAYMENT
        ])
            ->useAsSubtraction();
    }

    public static function discount(array $args): static
{
        return static::build($args)->useAsSubtraction();
    }

    public function useAsSubtraction(): static
{
        $this->setOperation(static::OPERATION_TYPE_SUBTRACT);
        return $this;
    }

    /**
     * @param  string  $operation
     *
     * @throws Exception
     */
    protected function setOperation(string $operation): void
    {
        if (!self::isValidOperation($operation)) {
            throw new Exception("Operation {$operation} not allowed.");
        }

        $this->_operation = $operation;
    }

    public static function isValidOperation(string $operation): bool
    {
        return in_array($operation, self::OPERATIONS, true);
    }

    public static function build(array $args): static
{
        $preset = static::preset(
            value: $args['value'] ?? 0,
            type: $args['type'] ?? self::VALUE_TYPE_PERCENT,
            operation: $args['operation'] ?? self::OPERATION_TYPE_ADD,
            method: $args['method'] ?? self::METHOD_CASH,
            label: $args['label'] ?? null,
            confirmation: $args['confirmation'] ?? null
        );

        $instance = new static();

        $instance->setValue($preset['value']);
        $instance->setType($preset['type']);
        $instance->setOperation($preset['operation']);
        $instance->setMethod($preset['method']);
        $instance->setLabel($preset['label']);
        $instance->setConfirmation($preset['confirmation']);

        return $instance;
    }

    public static function preset(
        float $value,
        ?string $label,
        ?string $confirmation = null,
        string $method = self::METHOD_CASH,
        string $operation = self::OPERATION_TYPE_ADD,
        string $type = self::VALUE_TYPE_PERCENT,
    ): array {
        return compact('label', 'value', 'method', 'operation', 'type', 'confirmation');
    }

    /**
     * @param  string  $method
     *
     * @throws Exception
     */
    protected function setMethod(string $method): void
    {
        if (!self::isValidMethod($method)) {
            throw new Exception("Method {$method} not allowed.");
        }
        $this->_method = $method;
    }

    public static function isValidMethod(string $method): bool
    {
        return in_array($method, self::PAYMENT_METHODS, $method);
    }

    /**
     * @param  string|null  $label
     */
    protected function setLabel(?string $label): void
    {
        $this->_label = $label;
    }

    /**
     * @param  string|null  $confirmation
     */
    protected function setConfirmation(?string $confirmation): void
    {
        $this->_confirmation = $confirmation;
    }

    public static function tax(array $args): static
{
        return static::build($args)->useAsAddition();
    }

    public function useAsAddition(): static
{
        $this->setOperation(static::OPERATION_TYPE_ADD);
        return $this;
    }

    public static function collect(array $aggregations): array
    {
        return array_map(fn(array $aggregation) => static::build($aggregation), $aggregations);
    }

    public function useValuePercent(float|int $value): static
{
        $this->setValue($value);
        $this->useTypePercent();
        return $this;
    }

    protected function setValue(float $value)
    {
        $this->_value = $value;
    }

    public function useTypePercent(): static
{
        $this->setType(static::VALUE_TYPE_PERCENT);
        return $this;
    }

    /**
     * @param  string  $type
     *
     * @throws Exception
     */
    protected function setType(string $type): void
    {
        if (!self::isValidType($type)) {
            throw new Exception("Type {$type} not allowed.");
        }

        $this->_type = $type;
    }

    public static function isValidType(string $type): bool
    {
        return in_array($type, self::VALUE_TYPES, true);
    }

    public function useValueFix(float|int $value): static
{
        $this->setValue($value);
        $this->useTypeFix();
        return $this;
    }

    public function useTypeFix(): static
{
        $this->setType(static::VALUE_TYPE_FIX);
        return $this;
    }

    public function isSubtraction(): bool
    {
        return $this->getOperation() === static::OPERATION_TYPE_SUBTRACT;
    }

    /**
     * @return string
     */
    protected function getOperation(): string
    {
        return $this->_operation;
    }

    public function rate(float|int $amount, bool $absolute = true): float|int
    {

        $value = 0.0;

        if ($amount < 1 || $this->getValue() < 0) {
            return $value;
        }

        if ($this->getType() === self::VALUE_TYPE_PERCENT) {
            $value = ($amount / 100) * $this->getValue();
        } else {
            $value = $this->getValue();
        }

        if ($absolute || $this->isAddition()) {
            return $value;
        }

        return $value * -1;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->_value;
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        return $this->_type;
    }

    public function isAddition(): bool
    {
        return $this->getOperation() === static::OPERATION_TYPE_ADD;
    }

    public function toArray()
    {
        return [
            'label' => $this->getLabel(),
            'value' => $this->getValue(),
            'method' => $this->getMethod(),
            'operation' => $this->getOperation(),
            'type' => $this->getType(),
            'confirmation' => $this->getConfirmation(),
        ];
    }

    /**
     * @return string|null
     */
    protected function getLabel(): ?string
    {
        return $this->_label;
    }

    /**
     * @return string
     */
    protected function getMethod(): string
    {
        return $this->_method;
    }

    /**
     * @return string|null
     */
    protected function getConfirmation(): ?string
    {
        return $this->_confirmation;
    }
}
