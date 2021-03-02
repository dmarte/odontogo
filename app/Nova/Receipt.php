<?php

namespace App\Nova;

use App\Models\Document;
use App\Nova\Actions\DocumentPrintAction;
use App\Nova\Metrics\ReceiptsIncomeByPeriod;
use Eminiarts\Tabs\Tabs;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

class Receipt extends Resource
{
    use SearchesRelations;

    public static $tableStyle = 'tight';
    public static $with = ['receiver'];
    public static $perPageOptions = [10, 25, 50, 75, 100];
    public static $preventFormAbandonment = true;
    public static $priority = 0;
    public static $model = \App\Models\Receipt::class;
    public static $search = [
        'code',
        'sequence_value',
        'sequence_number',
    ];
    public static $globalSearchRelations = [
        'provider' => ['name', 'code'],
        'receiver' => ['name', 'code'],
    ];
    public static $searchRelations = [
        'provider' => ['name', 'code'],
        'receiver' => ['name', 'code'],
    ];

    public function title()
    {
        return "{$this->code} ($ ".number_format($this->amount_paid).")";
    }

    public function subtitle()
    {
        return $this->receiver->name;
    }

    public static function group()
    {
        return __('Branch');
    }

    public static function label()
    {
        return __('Payments Receipts');
    }

    public static function singularLabel()
    {
        return __('Payment Receipt');
    }

    public function fieldsForUpdate()
    {
        return [
            // emitted_at
            Date::make(__('Emitted at'), 'emitted_at')
                ->rules(['required', 'date'])
                ->default(now()->format('Y-m-d')),
            // paid_at
            Date::make(__('Paid at'), 'paid_at')
                ->rules(['required', 'date'])
                ->default(now()->format('Y-m-d')),
            // receiver
            BelongsTo::make(__('Patient'), 'receiver', Patient::class)
                ->rules([
                    'required',
                    'numeric',
                ])
                ->searchable()
                ->showCreateRelationButton()
                ->withSubtitles(),

            Heading::make(__('Payment gateway')),
            Number::make(__('Cash'), 'data->amount->cash')
                ->help(__('What is the amount paid in cash?'))
                ->default(0)
                ->rules(['required', 'numeric', 'min:0'])
                ->min(0),
            Number::make(__('Credit card'), 'data->amount->credit_card')
                ->help(__('What is the amount paid with credit card?'))
                ->default(0)
                ->rules(['required', 'numeric', 'min:0'])
                ->min(0),
            Number::make(__('Bank transfer'), 'data->amount->bank_transfer')
                ->help(__('What is the amount paid with bank transfer?'))
                ->default(0)
                ->rules(['required', 'numeric', 'min:0'])
                ->min(0),
            Number::make(__('Credit note'), 'data->amount->credit_note')
                ->help(__('Set this field if a credit should be applied to this patient.'))
                ->default(0)
                ->rules(['required', 'numeric', 'min:0'])
                ->min(0),
            Heading::make(__('Administrative area')),
            Number::make(__('Credit card confirmation'), 'data->confirmation->credit_card')
                ->rules(['nullable', 'numeric']),
            Number::make(__('Bank transfer confirmation'), 'data->confirmation->bank_transfer')
                ->rules(['nullable', 'numeric']),
        ];
    }

    public function fieldsForCreate(Request $request)
    {
        return [
            Hidden::make('provider_contact_id')->default($request->user()->member->contact_id),
            Hidden::make('team_id')->default($request->user()->team->id),
            Hidden::make('currency')->default($request->user()->team->currency),
            Hidden::make('exchange_currency')->default($request->user()->team->currency),
            Hidden::make('exchange_rate')->default(1),
            Hidden::make('kind')->default(Document::KIND_PAYMENT_RECEIPT),
            Hidden::make('paid')->default(0),
            Hidden::make('completed')->default(0),
            Hidden::make('expire_at')->default(now()->format('Y-m-d')),
            Hidden::make('cancelled')->default(0),
            Hidden::make('sequence_id')->default($request->user()->team->receiptSequence->id),
            Hidden::make('category_attribute_id')->default(\App\Models\Catalog::income()->first()->id),
            Hidden::make('provider_contact_id')->default($request->user()->member->contact_id),
            Hidden::make('author_user_id')->default($request->user()->id),
            Hidden::make('completed_by_user_id')->default($request->user()->id),
            Hidden::make('updated_by_user_id')->default($request->user()->id),
            // emitted_at
            Date::make(__('Emitted at'), 'emitted_at')->rules(['required', 'date'])->default(now()->format('Y-m-d')),
            // Paid at
            Date::make(__('Paid at'), 'paid_at')->rules(['required', 'date'])->default(now()->format('Y-m-d')),
            // receiver
            BelongsTo::make(__('Patient'), 'receiver', Patient::class)->rules([
                'required',
                'numeric',
            ])
                ->searchable()
                ->showCreateRelationButton()
                ->withSubtitles(),
            Number::make(__('Cash'), 'data->amount->cash')
                ->help(__('What is the amount paid in cash?'))
                ->default(0)
                ->rules(['required', 'numeric', 'min:0'])
                ->min(0),
            Number::make(__('Credit card'), 'data->amount->credit_card')
                ->help(__('What is the amount paid with credit card?'))
                ->default(0)
                ->rules(['required', 'numeric', 'min:0'])
                ->min(0),
            Number::make(__('Bank transfer'), 'data->amount->bank_transfer')
                ->help(__('What is the amount paid with bank transfer?'))
                ->default(0)
                ->rules(['required', 'numeric', 'min:0'])
                ->min(0),
            Number::make(__('Credit note'), 'data->amount->credit_note')
                ->help(__('Set this field if a credit should be applied to this patient.'))
                ->default(0)
                ->rules(['required', 'numeric', 'min:0'])
                ->min(0),
            Heading::make(__('Administrative area')),
            Number::make(__('Credit card confirmation'), 'data->confirmation->credit_card')
                ->rules(['nullable', 'numeric']),
            Number::make(__('Bank transfer confirmation'), 'data->confirmation->bank_transfer')
                ->rules(['nullable', 'numeric']),

            // Sub-Category
            Hidden::make('subcategory_attribute_id')->default(70),
        ];
    }

    public function fieldsForDetail()
    {
        return [
            (new Tabs($this->resource->code, [
                __('Receipt')      => [
                    Number::make(__('Total paid'), 'amount_paid')->displayUsing(fn($value) => number_format($value))->default(0),
                    Number::make(__('Pending'), 'balance')->displayUsing(fn($value) => number_format($value)),
                    Number::make(__('Amount to pay'), 'total')->displayUsing(fn($value) => number_format($value)),
                    Number::make(__('Procedures'), 'quantity')->displayUsing(fn($value) => number_format($value)),
                    Heading::make(__('Details')),
                    // Sequence
                    Text::make(__('Code'), 'sequence_value'),
                    Text::make(__('Number'), 'sequence_number'),
                    BelongsTo::make(__('Emitted by'), 'author', User::class)->viewable(false),
                    // emitted_at
                    Date::make(__('Emitted at'), 'emitted_at')->format('dddd D, MMMM YYYY'),
                    // paid_at
                    Date::make(__('Paid at'), 'paid_at')->format('dddd D, MMMM YYYY'),
                    // receiver
                    BelongsTo::make(__('Patient'), 'receiver', Patient::class),

                    Heading::make(__('Administrative area')),
                    // Sub-Category
                    BelongsTo::make(__('Sub-Catalog'), 'subcategory', Catalog::class)->viewable(false),
                    // Payer
                    BelongsTo::make(__('Payer'), 'payer', Patient::class)->viewable(false),
                ],
                __('Distribution') => [
                    HasMany::make(__('Transactions'), 'items', ReceiptTransaction::class),
                ],
            ]))
                ->withToolbar()
                ->defaultSearch(false),


        ];
    }

    public function fieldsForIndex()
    {
        return [
            Text::make(__('Number'), 'sequence_number'),
            Date::make(__('Paid at'), 'paid_at')->format('dddd D, MMMM YYYY'),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class)->viewable(false),
            Number::make(__('Amount paid'), 'amount_paid')->displayUsing(fn($value) => number_format($value)),
        ];
    }

    public function fields(Request $request)
    {
        return [
            BelongsTo::make(__('Sub-Catalog'), 'subcategory', Catalog::class)->onlyOnDetail(),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class),
            BelongsTo::make(__('Payer'), 'payer', Patient::class),
            HasMany::make(__('Transactions'), 'items', ReceiptTransaction::class),
        ];
    }

    public function cards(Request $request)
    {
        return [
            (new ReceiptsIncomeByPeriod())->defaultRange('TODAY'),
//            (new DocumentSummaryCard())->field('total')->label('Total')->onlyOnDetail()->width('1/2'),
//            (new DocumentSummaryCard())->field('change')->label('Change amount')->onlyOnDetail(),
//            (new DocumentSummaryCard())->field('balance')->label('Pending')->onlyOnDetail(),
        ];
    }

    public function actions(Request $request)
    {
        return [
            DocumentPrintAction::make()->showOnTableRow()->showOnDetail()->withoutConfirmation(),
        ];
    }
}
