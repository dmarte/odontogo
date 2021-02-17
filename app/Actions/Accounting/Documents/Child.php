<?php


namespace App\Actions\Accounting\Documents;

use App\Actions\Accounting\Interfaces\Summarizable;
use App\Actions\Accounting\Interfaces\Transformable;
use App\Actions\Accounting\Traits\HasDocumentSharedData;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Item;
use App\Models\Product;
use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

/**
 * Class DocumentItem
 * @package App\Actions\Accounting
 * @property-read int $id
 * @property string $title
 * @property string $description
 * @property int $document_id
 * @property int $product_id
 * @property float $discount_rate
 * @property array $data
 * @method static static create(array $array)
 */
class Child extends Model implements Transformable, Summarizable
{
    use HasDocumentSharedData;

    protected const ENTITY_PROVIDER = Contact::class;
    protected const ENTITY_RECEIVER = Contact::class;

    public $table = 'documents_items';

    protected $casts = [
        'data' => 'array'
    ];

    protected $fillable = [
        'document_id',
        'product_id',
        'discount_rate',
    ];

    public function sanitize() :void {
        $this->data = ChildData::build($this->data)->toArray();
        $this->price = $this->product->price;
        $this->summarize();
        $this->document->summarize();
    }

    public static function booted()
    {
        static::creating(function (Child $child) {
            $child->sanitize();
        });

        static::updated(function(Child $child) {
            $child->sanitize();
        });

        static::deleted(function(Child $child) {
            $this->document->summarize();
        });

        parent::booted();
    }

    public static function register(Head $head, Team $team, array $resource): static
    {
        Arr::set($resource, 'document.id', $head->id);
        Arr::set($resource, 'team.id', $team->id);
        Arr::set($resource, 'author.creator.id', $team->membership->user_id);
        Arr::set($resource, 'author.updated.id', $team->membership->user_id);

        $model = new static(
            static::toModelArray($resource)
        );

        $model->save();

        return $model;
    }

    public static function toModelArray(array $resource): array
    {
        return [
            'title' => Arr::get($resource, 'title'),
            'description' => Arr::get($resource, 'description'),
            'data' => Arr::get($resource, 'data'),
            'currency' => Arr::get($resource, 'summary.currency', Arr::get($resource, 'currency', 'USD')),
            'product_id' => Arr::get($resource, 'product.id'),
            'quantity' => Arr::get($resource, 'summary.quantity'),
            'amount' => Arr::get($resource, 'summary.amount'),
            'price' => (float) Arr::get($resource, 'summary.price'),
            'taxes' => (float) Arr::get($resource, 'summary.taxes'),
            'discounts' => (float) Arr::get($resource, 'summary.discounts'),
            'subtotal' => (float) Arr::get($resource, 'summary.subtotal'),
            'total' => (float) Arr::get($resource, 'summary.total'),
            'amount_paid' => (float) Arr::get($resource, 'summary.paid'),
            'balance' => (float) Arr::get($resource, 'summary.balance'),
            'change' => (float) Arr::get($resource, 'summary.change'),
            'paid' => (bool) Arr::get($resource, 'status.paid', false),
            'completed' => (bool) Arr::get($resource, 'status.completed', false),
            'verified' => (bool) Arr::get($resource, 'status.verified', false),
            'emitted_at' => Arr::get($resource, 'date.emitted'),
            'expire_at' => Arr::get($resource, 'date.expire'),
            'completed_at' => Arr::get($resource, 'date.completed'),
            'cancelled_at' => Arr::get($resource, 'date.cancelled'),
            'category_attribute_id' => Arr::get($resource, 'category.id'),
            'subcategory_attribute_id' => Arr::get($resource, 'subcategory.id'),
            'provider_contact_id' => Arr::get($resource, 'provider.id'),
            'receiver_contact_id' => Arr::get($resource, 'receiver.id'),
            'author_user_id' => Arr::get($resource, 'author.id'),
            'team_id' => Arr::get($resource, 'team.id'),
            'document_id' => Arr::get($resource, 'document.id'),
        ];
    }

    /**
     * @param  Item  $model
     * @return array
     */
    public static function toResourceArray(Model $model): array
    {
        return [
            'id' => $model->id,
            'title' => $model->title,
            'description' => $model->description,
            'document' => ['id' => $model->document_id],
            'product' => ['id' => $model->product_id],
            'provider' => ['id' => $model->provider_contact_id],
            'receiver' => ['id' => $model->receiver_contact_id],
            'category' => ['id' => $model->category_attribute_id],
            'subcategory' => ['id' => $model->subcategory_attribute_id],
            'data' => $model->data,
            'summary' => [
                'currency' => $model->currency,
                'quantity' => $model->quantity,
                'amount' => $model->amount,
                'price' => $model->price,
                'taxes' => $model->taxes,
                'discounts' => $model->discounts,
                'subtotal' => $model->subtotal,
                'total' => $model->total,
                'balance' => $model->balance,
                'paid' => $model->amount_paid,
                'change' => $model->change,
            ],
            'status' => [
                'paid' => $model->paid,
                'completed' => $model->completed,
                'cancelled' => $model->cancelled,
                'verified' => $model->verified,
            ],
            'date' => [
                'emitted' => $model->emitted_at?->format('Y-m-d'),
                'expire' => $model->expire_at?->format('Y-m-d'),
                'paid' => $model->paid_at?->format('Y-m-d'),
                'completed' => $model->completed_at?->format('Y-m-d'),
                'cancelled' => $model->cancelled_at?->format('Y-m-d'),
                'verified' => $model->verified_at?->format('Y-m-d'),
            ]
        ];
    }

    public static function buildFromAggregationArray(array $aggregation): static
    {
        return self::buildFromArrayResource([
            'data' => $aggregation,
            'summary' => [
                'quantity' => 1,
                'price' => $aggregation['value'] ?? 0,
                'paid' => $aggregation['type'] === Aggregation::VALUE_TYPE_PAYMENT ? $aggregation['value'] : 0,
            ]
        ])->summarize();
    }

    public function summary(string $field): float|int
    {
        // Verify if the value already belongs to an attribute defined
        // and that attribute has a value.
        if ($this->getAttribute($field) > 0) {

            return $this->getAttribute($field);

        }

        return 0;
    }

    public function summarizedBalance(): float|int
    {
        return $this->summarizedTotal() - $this->summarizedAmountPaid();
    }

    public function summarizedTotal(): float|int
    {
        return $this->summarizedSubtotal() + $this->summarizedTaxes();
    }

    public function summarizedSubtotal(): float|int
    {
        return $this->summarizedAmount() + $this->summarizedDiscounts();
    }

    public function summarizedAmount(): float|int
    {
        return $this->summarizedPrice() * $this->summarizedQuantity();
    }

    public function summarizedPrice(): float|int
    {
        return (float) $this->getAttributeValue('price');
    }

    public function summarizedQuantity(): float|int
    {
        return (float) $this->getAttributeValue('quantity');
    }

    public function summarizedDiscounts(): float|int
    {
        if ($this->discount_rate < 1) {
            return 0;
        }

        return $this->summarizedPrice() - (($this->discount_rate * $this->price) / 100);
    }

    public function data(): ChildData
    {

        $data = $this->getAttributeValue('data');

        if (is_null($data)) {
            return ChildData::build();
        }

        if ($data instanceof ChildData) {

            return $data;

        }

        return ChildData::build($data);
    }

    public function summarizedTaxes(): float|int
    {
        return $this->data()->getTotalTaxes($this->summarizedAmount(), true);
    }

    public function summarizedAmountPaid(): float|int
    {
        return (float) $this->getAttributeValue('amount_paid');
    }

    public function pay(float $amount): static
    {
        $this->change = $this->calculateAmountCashBack($amount);
        $this->amount_paid += $this->calculateAmountToPay($amount);

        $this->summarize();

        return $this;
    }

    public function calculateAmountCashBack(float $amount): float
    {

        if ($amount > $this->balance) {

            return $amount - $this->balance;

        }

        return 0;
    }

    public function calculateAmountToPay(float $amount): float
    {

        if ($amount > $this->balance) {

            return $amount - $this->calculateAmountCashBack($amount);

        }

        return $amount;

    }

    public function document() : BelongsTo {
        return $this->belongsTo(Document::class);
    }

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
