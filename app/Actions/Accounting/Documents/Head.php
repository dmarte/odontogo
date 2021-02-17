<?php

namespace App\Actions\Accounting\Documents;

use App\Actions\Accounting\Interfaces\Summarizable;
use App\Actions\Accounting\Interfaces\Transformable;
use App\Actions\Accounting\Traits\HasDocumentSharedData;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Receipt;
use App\Models\Sequence;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use JetBrains\PhpStorm\ArrayShape;

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
class Head extends Model implements Transformable, Summarizable
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

    protected $attributes = [
        'kind' => self::KIND_CASH_BILL,
    ];

    protected $casts = [
        'exchange_rate'      => 'float',
        'counter'            => 'int',
        'sequence_expire_at' => 'date:Y-m-d',
        'sequence_number'    => 'int',
    ];

    protected $fillable = [
        'kind',
        'sequence_id',
        'sequence_prefix',
        'sequence_length',
        'sequence_number',
        'sequence_expire_at',
        'sequence_value',
        'counter',
        'code',
        'exchange_rate',
        'exchange_currency',
    ];

    public function getForeignKey()
    {
        return 'document_id';
    }

    public function distribute(User $author, Team $team, array $payments = [], ?int $paidByContactId = null)
    {
        // Create the receipt document type related to this document.
        $sequence = $team->sequenceForReceipt();

        /* @var $receipt Receipt */
        $receipt = Receipt::create([
            'currency'                 => $this->currency,
            'sequence_id'              => $sequence->id,
            'team_id'                  => $team->id,
            'category_attribute_id'    => $this->category_attribute_id,
            'subcategory_attribute_id' => $this->subcategory_attribute_id,
            'provider_contact_id'      => $this->provider_contact_id,
            'receiver_contact_id'      => $this->receiver_contact_id,
            'paid_by_contact_id'       => $paidByContactId ?? $this->receiver_contact_id,
            'author_user_id'           => $author->id,
            'completed_by_user_id'     => $author->id,
            'updated_by_user_id'       => $author->id,
            'emitted_at'               => now()->format('Y-m-d'),
            'expire_at'                => now()->format('Y-m-d'),
            'paid_at'                  => now()->format('Y-m-d'),
            'quantity'                 => 0,
            'price'                    => 0,
            'amount_paid'              => 0,
        ]);

        foreach ($payments as $payment) {

            if ($payment['value'] < 1) {
                continue;
            }

            $receipt->items()->create([
                'data'                     => $payment,
                'product_id'               => null,
                'currency'                 => $receipt->currency,
                'quantity'                 => 1,
                'price'                    => $payment['value'],
                'amount_paid'              => $payment['value'],
                'team_id'                  => $receipt->team_id,
                'category_attribute_id'    => $receipt->category_attribute_id,
                'subcategory_attribute_id' => $receipt->subcategory_attribute_id,
                'provider_contact_id'      => $receipt->provider_contact_id,
                'receiver_contact_id'      => $receipt->receiver_contact_id,
                'paid_by_contact_id'       => $receipt->paid_by_contact_id,
                'author_user_id'           => $author->id,
                'completed_by_user_id'     => $author->id,
                'updated_by_user_id'       => $author->id,
            ]);

            $items = $this->items->filter(fn(Child $item) => !$item->paid);

            /* @var $item Child */
            foreach ($items as $item) {
                $item->pay($payment['value'] ?? 0)->save();
            }
        }

        $this->summarize()->save();

        $receipt
            ->summarize()
            ->save();

        $this->related()->attach($receipt->id);
    }

    public static function register(array $resource, User $author, Team $team): static
    {
        Arr::set($resource, 'team.id', $team->id);
        Arr::set($resource, 'author.creator.id', $team->membership->user_id);
        Arr::set($resource, 'author.updated.id', $team->membership->user_id);

        $document = new static(static::toModelArray($resource));

        // Save to get the ID used to register items
        $document->save();

        if (!empty($resource['items']) && is_array($resource['items'])) {
            $items = $resource['items'];

            foreach ($items as $item) {
                Child::register($document, $team, $item);
            }
        }

        if (!empty($resource['payments']) && is_array($resource['payments'])) {
            $document->distribute($author, $team, $resource['payments']);
        }

        if (!empty($resource['with'])) {
            $document->load($resource['with']);
        }

        return $document;
    }

    public static function toModelArray(array $resource): array
    {
        return [
            'title'                    => Arr::get($resource, 'title'),
            'description'              => Arr::get($resource, 'description'),
            'kind'                     => Arr::get($resource, 'kind', Document::KIND_CASH_BILL),
            'sequence_id'              => Arr::get($resource, 'sequence.id'),
            'sequence_prefix'          => Arr::get($resource, 'sequence.prefix'),
            'sequence_length'          => Arr::get($resource, 'sequence.length'),
            'sequence_number'          => Arr::get($resource, 'sequence.next'),
            'sequence_expire_at'       => Arr::get($resource, 'date.expire'),
            'sequence_value'           => Arr::get($resource, 'sequence.value'),
            'category_attribute_id'    => Arr::get($resource, 'category.id'),
            'subcategory_attribute_id' => Arr::get($resource, 'subcategory.id'),
            'provider_contact_id'      => Arr::get($resource, 'provider.id'),
            'receiver_contact_id'      => Arr::get($resource, 'receiver.id'),
            'amount'                   => (float) Arr::get($resource, 'summary.amount'),
            'taxes'                    => (float) Arr::get($resource, 'summary.taxes'),
            'amount_paid'              => (float) Arr::get($resource, 'summary.paid'),
            'price'                    => (float) Arr::get($resource, 'summary.price'),
            'quantity'                 => (float) Arr::get($resource, 'summary.quantity'),
            'discounts'                => (float) Arr::get($resource, 'summary.discounts'),
            'subtotal'                 => (float) Arr::get($resource, 'summary.subtotal'),
            'total'                    => (float) Arr::get($resource, 'summary.total'),
            'balance'                  => (float) Arr::get($resource, 'summary.balance'),
            'change'                   => (float) Arr::get($resource, 'summary.change'),
            'currency'                 => Arr::get($resource, 'summary.currency', 'USD'),
            'exchange_currency'        => Arr::get($resource, 'summary.exchange.currency', 'USD'),
            'exchange_rate'            => (float) Arr::get($resource, 'summary.exchange.rate', 1),
            'author_user_id'           => Arr::get($resource, 'author.created.id'),
            'updated_by_user_id'       => Arr::get($resource, 'author.updated.id', Arr::get($resource, 'author.created.id')),
            'completed_by_user_id'     => Arr::get($resource, 'author.completed.id'),
            'cancelled_by_user_id'     => Arr::get($resource, 'author.cancelled.id'),
            'deleted_by_user_id'       => Arr::get($resource, 'author.deleted.id'),
            'paid'                     => (bool) Arr::get($resource, 'status.paid', false),
            'completed'                => (bool) Arr::get($resource, 'status.completed', false),
            'cancelled'                => (bool) Arr::get($resource, 'status.cancelled', false),
            'verified'                 => (bool) Arr::get($resource, 'status.verified', false),
            'team_id'                  => Arr::get($resource, 'team.id'),
            'emitted_at'               => Arr::get($resource, 'date.emitted'),
            'expire_at'                => Arr::get($resource, 'date.expire'),
            'paid_at'                  => Arr::get($resource, 'date.paid'),
            'completed_at'             => Arr::get($resource, 'date.completed'),
            'cancelled_at'             => Arr::get($resource, 'date.cancelled'),
            'verified_at'              => Arr::get($resource, 'date.verified'),
        ];
    }

    public static function buildFromArrayResource(array $resource): static
    {
        return static::buildFromArrayModel(

            static::toModelArray($resource),

            array_map(function (array $item) use ($resource) {

                $item['team'] = $resource['team'] ?? ['id' => null];

                return Child::buildFromArrayResource($item);

            }, $resource['items'] ?? [])
        );
    }

    public static function buildFromArrayModel(array $model, array $items = []): static
    {
        if (empty($model['currency'])) {

            $model['currency'] = 'USD';

        }

        if (empty($model['exchange_currency'])) {

            $model['exchange_currency'] = $model['currency'];

        }

        /* @var $document static */
        $document = new static($model);

        $document->save();

        if (count($items) > 0) {

            $document->items()->saveMany($items);

        }

        $document->summarize();

        return $document;
    }

    public function items(): HasMany
    {
        return $this->hasMany(self::ENTITY_ITEM, 'document_id');
    }

    #[ArrayShape([
        'currency'  => 'string',
        'quantity'  => 'int',
        'price'     => 'float',
        'amount'    => 'float',
        'discounts' => 'float',
        'taxes'     => 'float',
        'subtotal'  => 'float',
        'total'     => 'float',
        'paid'      => 'float',
        'balance'   => 'float',
    ])]
    public static function itemsToSummary(
        array $resourceItems,
        ?int $teamId
    ): array {
        $items = collect(
            array_map(function (array $item) use ($teamId) {

                $item['team'] = ['id' => $teamId];

                return Child::buildFromArrayResource($item);

            }, $resourceItems)
        );

        return [
            'currency'  => $items->first()?->currency,
            'quantity'  => $items->sum('quantity'),
            'price'     => $items->sum('price'),
            'amount'    => $items->sum('amount'),
            'discounts' => $items->sum('discounts'),
            'taxes'     => $items->sum('taxes'),
            'subtotal'  => $items->sum('subtotal'),
            'total'     => $items->sum('total'),
            'paid'      => $items->sum('amount_paid'),
            'balance'   => $items->sum('balance'),
        ];
    }

    /**
     * @param  Head  $document
     *
     * @return array
     */
    public static function toResourceArray(Model $document): array
    {
        return [
            'id'          => $document->id,
            'code'        => $document->code,
            'counter'     => $document->counter,
            'title'       => $document->title,
            'description' => $document->description,
            'kind'        => $document->kind,
            'sequence'    => [
                'id'     => $document->sequence_id,
                'prefix' => $document->sequence_prefix,
                'length' => $document->sequence_length,
                'number' => $document->sequence_number,
                'expire' => $document->sequence_expire_at?->format('Y-m-d'),
                'value'  => $document->sequence_value,
            ],
            'summary'     => [
                'amount'    => $document->amount,
                'paid'      => $document->amount_paid,
                'taxes'     => $document->taxes,
                'discounts' => $document->discounts,
                'subtotal'  => $document->subtotal,
                'total'     => $document->total,
                'balance'   => $document->balance ?? 0,
                'currency'  => $document->currency,
                'exchange'  => [
                    'rate'     => $document->exchange_rate,
                    'currency' => $document->exchange_currency,
                ],
            ],
            'status'      => [
                'paid'      => $document->paid,
                'completed' => $document->completed,
                'cancelled' => $document->cancelled,
                'verified'  => $document->verified,
            ],
            'date'        => [
                'emitted'   => $document->emitted_at?->format('Y-m-d'),
                'expire'    => $document->expire_at?->format('Y-m-d'),
                'paid'      => $document->paid_at?->format('Y-m-d'),
                'completed' => $document->completed_at?->format('Y-m-d'),
                'cancelled' => $document->cancelled_at?->format('Y-m-d'),
                'verified'  => $document->verified_at?->format('Y-m-d'),
            ],
            'category'    => [
                'id' => $document->category_attribute_id,
            ],
            'subcategory' => [
                'id' => $document->subcategory_attribute_id,
            ],
            'provider'    => [
                'id' => $document->provider_contact_id,
            ],
            'receiver'    => [
                'id' => $document->receiver_contact_id,
            ],
            'team'        => [
                'id' => $document->team_id,
            ],
            'author'      => [
                'payment'   => [
                    'id' => $document->paid_by_contact_id,
                ],
                'creator'   => [
                    'id' => $document->author_user_id,
                ],
                'completed' => [
                    'id' => $document->completed_by_user_id,
                ],
                'cancelled' => [
                    'id' => $document->cancelled_by_user_id,
                ],
                'updated'   => [
                    'id' => $document->updated_by_user_id,
                ],
                'deleted'   => [
                    'id' => $document->deleted_by_user_id,
                ],
            ],
            'items'       => array_map(function (Model $item) {
                return Child::toResourceArray($item);
            }, $document->items?->all() ?? []),
        ];
    }

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

            $document->code = $document->kind.str_pad($document->counter, 8, '0', STR_PAD_LEFT);

            if (!$document->getAttributeValue('title')) {
                $document->title = $document->title();
            }

            if (!$document->exchange_currency) {
                $document->exchange_currency = $document->currency;
            }

            if (!$document->exchange_rate) {
                $document->exchange_rate = 1;
            }

            $document->buildSequence();
        });

        parent::booted();
    }

    private function buildSequence(): void
    {
        $sequence = is_null($this->sequence_id) ? $this->receiver->sequence : $this->sequence;

        $this->sequence_prefix = $sequence?->prefix;
        $this->sequence_length = $sequence?->length;
        $this->sequence_number = $sequence?->next;
        $this->sequence_expire_at = $sequence?->expire_at?->format('Y-m-d');
        $this->sequence_value = $sequence?->next_formatted;

        $sequence?->increase();
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function related(): MorphToMany
    {
        return $this->morphToMany(Document::class, 'model', 'documents_relations')->with('items');
    }

    /**
     * Add a resource item to the given head resource.
     *
     * @param  array  $resourceItem
     *
     * @return $this
     */
    public function add(array $resourceItem): static
    {
        if (!Arr::has($resourceItem, 'team.id')) {

            Arr::set($resourceItem, 'team.id', $this->team_id);

        }
        $this->items()->save(
            Child::buildFromArrayResource($resourceItem)
        );

        $this->refresh();

        $this->summarize();

        return $this;
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }

    public function summarizedQuantity(): float|int
    {
        return $this->summary('quantity');
    }

    public function summary(string $field): float|int
    {
        return $this->items?->sum($field) ?? 0;
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
