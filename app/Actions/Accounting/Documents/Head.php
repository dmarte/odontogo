<?php

namespace App\Actions\Accounting\Documents;

use App\Actions\Accounting\Interfaces\Summarizable;
use App\Actions\Accounting\Traits\HasDocumentSharedData;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Sequence;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Class Document
 *
 * @package App\Models
 * @property-read int $id
 * @property string $title
 * @property string $description
 * @property string $kind
 * @property int $sequence_id
 * @property string $sequence_prefix
 * @property string $sequence_length
 * @property string $sequence_number
 * @property string $sequence_expire_at
 * @property string $sequence_value
 * @property int $counter
 * @property string $code
 * @property float $exchange_rate
 * @property string $exchange_currency
 * @property int $provider_contact_id
 * @property int $receiver_contact_id
 * @property-read Team $team
 * @property-read Collection $items
 * @property-read Sequence $sequence
 * @method static create(array $array)
 */
class Head extends Model implements Summarizable
{
    use HasDocumentSharedData;

    protected const ENTITY_ITEM = Child::class;
    protected const ENTITY_PROVIDER = Contact::class;
    protected const ENTITY_RECEIVER = Contact::class;

    public const KIND_INVOICE_BUDGET = 'IB';
    public const KIND_CASH_BILL = 'CB';
    public const KIND_CREDIT_INVOICE = 'CI';
    public const KIND_PAYMENT_RECEIPT = 'PR';
    public const KIND_DEPOSIT = 'DP';
    public const KIND_DOCTOR_EVALUATION = 'DE';
    public const KIND_EXPENSE = 'EX';
    public const KIND_DEBIT_NOTE = 'DN';
    public const KIND_CREDIT_NOTE = 'CN';

    public const KINDS_INVOICES = [
        self::KIND_CASH_BILL,
        self::KIND_CREDIT_INVOICE,
    ];

    public const KINDS = [
        self::KIND_INVOICE_BUDGET,
        self::KIND_CASH_BILL,
        self::KIND_CREDIT_INVOICE,
        self::KIND_PAYMENT_RECEIPT,
        self::KIND_DEPOSIT,
        self::KIND_DOCTOR_EVALUATION,
        self::KIND_EXPENSE,
        self::KIND_CREDIT_INVOICE,
        self::KIND_DEBIT_NOTE,
    ];

    protected $primaryKey = 'id';
    public $table = 'documents';

    protected $attributes = [];

    protected $casts = [
        'exchange_rate'      => 'float',
        'counter'            => 'int',
        'sequence_expire_at' => 'date:Y-m-d',
        'sequence_number'    => 'int',
        'quantity'           => 'int',
        'price'              => 'float',
        'amount'             => 'float',
        'discounts'          => 'float',
        'taxes'              => 'float',
        'subtotal'           => 'float',
        'total'              => 'float',
        'amount_paid'        => 'float',
        'balance'            => 'float',
        'change'             => 'float',
        'paid'               => 'boolean',
        'completed'          => 'boolean',
        'cancelled'          => 'boolean',
        'verified'           => 'boolean',
        'data'               => 'array',
        'emitted_at'         => 'date:Y-m-d',
        'expire_at'          => 'date:Y-m-d',
        'paid_at'            => 'date:Y-m-d',
        'completed_at'       => 'date:Y-m-d',
        'verified_at'        => 'date:Y-m-d',
        'deleted_at'         => 'date:Y-m-d',
        'cancelled_at'       => 'date:Y-m-d',
    ];

    protected $fillable = [
        'data',
        'kind',
        'sequence_id',
        'sequence_prefix',
        'sequence_length',
        'sequence_number',
        'sequence_expire_at',
        'sequence_value',
        'wallet_attribute_id',
        'counter',
        'code',
        'exchange_rate',
        'exchange_currency',
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
    ];

    protected static function booted()
    {

        static::creating(function (Head $document) {

            $document->counter = Document::where('team_id', $document->team_id)
                    ->where(function (Builder $query) use ($document) {
                        if (in_array($document->kind, self::KINDS_INVOICES, true)) {
                            $query->whereIn('kind', self::KINDS_INVOICES);

                            return;
                        }

                        $query->where('kind', $document->kind);
                    })
                    ->max('counter') + 1;

            if (!$document->exchange_currency) {
                $document->exchange_currency = $document->currency;
            }

            if (!$document->exchange_rate) {
                $document->exchange_rate = 1;
            }

            $document->buildSequence();
            $document->buildCode();

            if (!$document->getAttributeValue('title')) {
                $document->buildTitle();
            }
        });

    }

    public function getForeignKey()
    {
        return 'document_id';
    }

    public function buildSequence(): void
    {
        $ignore = [
            static::KIND_EXPENSE,
        ];

        if (in_array($this->kind, $ignore, true)) {
            return;
        }

        $sequence = is_null($this->sequence_id) ? $this->receiver->sequence : $this->sequence;

        $this->sequence_prefix = $sequence?->prefix;
        $this->sequence_length = $sequence?->length;
        $this->sequence_number = $sequence?->next;
        $this->sequence_expire_at = $sequence?->expire_at?->format('Y-m-d');
        $this->sequence_value = $sequence?->next_formatted;

        $sequence?->increase();
    }

    public function buildCode(): void
    {
        $this->code = $this->kind.str_pad($this->counter, $this->sequence_length, '0', STR_PAD_LEFT);
    }

    public function buildTitle(): void
    {
        $this->title = $this->title();
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function related(): MorphToMany
    {
        return $this->morphToMany(Document::class, 'model', 'documents_relations')->with('items');
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }

    public function summary(string $field): float|int
    {
        return $this->items?->sum($field) ?? 0;
    }

    public function summarizedQuantity(): float|int
    {
        return $this->summary('quantity');
    }

    public function summarizedPrice(): float|int
    {
        return $this->summary('price');
    }

    public function summarizedChange(): float|int
    {
        return $this->summary('change');
    }

    public function summarizedDiscounts(): float|int
    {
        return $this->summary('discounts');
    }

    public function summarizedTaxes(): float|int
    {
        return $this->summary('taxes');
    }

    public function summarizedAmount(): float|int
    {
        return $this->summary('amount');
    }

    public function summarizedSubtotal(): float|int
    {
        return $this->summary('subtotal');
    }

    public function summarizedTotal(): float|int
    {
        return $this->summary('total');
    }

    public function summarizedBalance(): float|int
    {
        return $this->summary('balance');
    }

    public function summarizedAmountPaid(): float|int
    {
        return $this->summary('amount_paid');
    }
}
