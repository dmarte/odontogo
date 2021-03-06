<?php


namespace App\Actions\Accounting\Documents;

use App\Actions\Accounting\Interfaces\Summarizable;
use App\Actions\Accounting\Traits\HasDocumentSharedData;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Product;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DocumentItem
 *
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
class Child extends Model implements Summarizable
{
    use HasDocumentSharedData;
    use SoftDeletes;

    protected const ENTITY_PROVIDER = Contact::class;
    protected const ENTITY_RECEIVER = Contact::class;

    public $table = 'documents_items';

    protected $casts = [
        'data'          => 'array',
        'discount_rate' => 'float',
        'quantity'      => 'int',
        'price'         => 'float',
        'amount'        => 'float',
        'discounts'     => 'float',
        'taxes'         => 'float',
        'subtotal'      => 'float',
        'total'         => 'float',
        'amount_paid'   => 'float',
        'balance'       => 'float',
        'change'        => 'float',
        'paid'          => 'boolean',
        'completed'     => 'boolean',
        'cancelled'     => 'boolean',
        'verified'      => 'boolean',
        'emitted_at'    => 'date:Y-m-d',
        'expire_at'     => 'date:Y-m-d',
        'paid_at'       => 'date:Y-m-d',
        'completed_at'  => 'date:Y-m-d',
        'verified_at'   => 'date:Y-m-d',
        'deleted_at'    => 'date:Y-m-d',
        'cancelled_at'  => 'date:Y-m-d',
    ];

    protected $fillable = [
        'document_id',
        'product_id',
        'discount_rate',
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
        'data',
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
        'wallet_attribute_id',
    ];

    public function sanitize(): void
    {
        if (is_null($this->wallet_attribute_id)) {
            $this->wallet_attribute_id = $this->document->wallet_attribute_id ?? $this->team->wallet_attribute_id;
        }
        if(is_null($this->emitted_at)) {
            $this->emitted_at = $this->document->emitted_at;
        }

        if (is_null($this->receiver_contact_id)) {
            $this->receiver_contact_id = $this->document->receiver_contact_id;
        }

        $this->data = array_merge((array) $this->data, [
            'kind' => $this->document->kind,
        ]);

        $this->currency = $this->currency ?? $this->document->currency;
        $this->team_id = $this->document->team_id;
        if (is_null($this->price)) {
            $this->price = $this->product->price;
        }
        if ($this->quantity < 1) {
            $this->quantity = 1;
        }
        $this->summarize();
    }

    public static function booted()
    {
        static::creating(function (Child $child) {
            $child->sanitize();
        });

        static::created(function (Child $child) {
            $child->document->summarize();
            $child->document->buildTitle();
            $child->document->save();
            // Wallets
            $child->wallet?->summarize('wallet_attribute_id');
        });

        static::updating(function (Child $child) {
            $child->sanitize();
        });

        static::updated(function (Child $child) {
            $child->document->summarize();
            $child->document->buildTitle();
            $child->document->save();
        });

        static::deleted(function (Child $child) {
            $child->document->summarize();
            $child->document->buildTitle();
            $child->document->save();
            $child->wallet?->summarize('wallet_attribute_id');
        });

        parent::booted();
    }

    public function wallet() : BelongsTo {
        return $this->belongsTo(Wallet::class, 'wallet_attribute_id');
    }

    public function document() : BelongsTo {
        return $this->belongsTo(Document::class);
    }

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class);
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
        if ($this->summarizedAmountPaid() > $this->summarizedTotal()) {
            return 0;
        }

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

        return ((($this->discount_rate * $this->summarizedPrice()) / 100) * $this->summarizedQuantity()) * -1;
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

    public function summarizedChange(): float|int  {

        if ($this->summarizedAmountPaid() > $this->summarizedTotal()) {

            return $this->summarizedAmountPaid() - $this->summarizedTotal();

        }

        return 0;
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
}
