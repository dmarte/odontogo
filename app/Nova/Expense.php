<?php

namespace App\Nova;

use App\Models\Document;
use Eminiarts\Tabs\Tabs;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

class Expense extends Resource
{
    use SearchesRelations;
    public static $globalSearchRelations = [
        'provider' => ['name', 'code'],
        'receiver' => ['name', 'code'],
    ];
    public static $searchRelations = [
        'provider' => ['name', 'code'],
        'receiver' => ['name', 'code'],
    ];
    public static $model = \App\Models\Expense::class;
    public static $priority = 2;
    public static $title = 'code';
    public static $search = [
        'code',
        'sequence_value',
        'sequence_number'
    ];

    public static function group()
    {
        return __('Branch');
    }

    public static function singularLabel()
    {
        return __('Expense');
    }

    public static function label()
    {
        return __('Expenses');
    }

    public function fieldsForCreate(Request $request)
    {
        return [
            Hidden::make('team_id')->default($request->user()->team->id),
            Hidden::make('currency')->default($request->user()->team->currency),
            Hidden::make('exchange_currency')->default($request->user()->team->currency),
            Hidden::make('exchange_rate')->default(1),
            Hidden::make('kind')->default(Document::KIND_EXPENSE),
            Hidden::make('paid')->default(0),
            Hidden::make('completed')->default(0),
            Hidden::make('cancelled')->default(0),
            Hidden::make('category_attribute_id')->default(\App\Models\Catalog::expenses()->first()->id),
            Hidden::make('subcategory_attribute_id')->default(\App\Models\Catalog::expensesGeneral()->first()->id),
            Hidden::make('author_user_id')->default($request->user()->id),
            Hidden::make('updated_by_user_id')->default($request->user()->id),
            // emitted_at
            Date::make(__('Invoice at'), 'emitted_at')
                ->rules(['required', 'date'])
                ->default(now()->format('Y-m-d')),
            // paid_at
            Date::make(__('Expire at'), 'expire_at')
                ->rules(['required', 'date'])
                ->default(now()->format('Y-m-d')),
            Text::make(__(strtolower($request->user()->team->country)."_tax_sequence_fiscal"), 'sequence_value'),
            Date::make(__(strtolower($request->user()->team->country)."_tax_sequence_fiscal_expiry"), 'sequence_expire_at'),
            Text::make(__("Invoice number"), 'sequence_number'),
            // provider
            BelongsTo::make(__('Provider'), 'provider', Provider::class)
                ->withoutTrashed()
                ->rules([
                    'required',
                    'numeric',
                ])
                ->searchable()
                ->showCreateRelationButton()
                ->withSubtitles(),
            // patient
            Number::make(__('Sub-Total'), 'subtotal')->default(0)->rules(['required', 'numeric', 'min:0']),
            Number::make(__('Discounts'), 'discounts')->default(0)->rules(['required', 'numeric', 'min:0']),
            Number::make(__('Taxes'), 'taxes')->default(0)->rules(['required', 'numeric', 'min:0']),
            Number::make(__('Total'), 'total')->default(0)->rules(['required', 'numeric', 'min:0']),
            Text::make(__('Notes'), 'description'),
        ];
    }

    public function fieldsForUpdate(Request $request)
    {
        return [
            Hidden::make('updated_by_user_id')->default($request->user()->id),
            // emitted_at
            Date::make(__('Invoice at'), 'emitted_at')
                ->rules(['required', 'date'])
                ->default(now()->format('Y-m-d')),
            // paid_at
            Date::make(__('Expire at'), 'expire_at')
                ->rules(['required', 'date'])
                ->default(now()->format('Y-m-d')),
            Text::make(__(strtolower($request->user()->team->country)."_tax_sequence_fiscal"), 'sequence_value'),
            Date::make(__(strtolower($request->user()->team->country)."_tax_sequence_fiscal_expiry"), 'sequence_expire_at'),
            Text::make(__("Invoice number"), 'sequence_number'),
            // provider
            BelongsTo::make(__('Provider'), 'provider', Provider::class)
                ->withoutTrashed()
                ->rules([
                    'required',
                    'numeric',
                ])
                ->searchable()
                ->showCreateRelationButton()
                ->withSubtitles(),
            // patient
            Number::make(__('Sub-Total'), 'subtotal')->default(0)->rules(['required', 'numeric', 'min:0']),
            Number::make(__('Discounts'), 'discounts')->default(0)->rules(['required', 'numeric', 'min:0']),
            Number::make(__('Taxes'), 'taxes')->default(0)->rules(['required', 'numeric', 'min:0']),
            Number::make(__('Total'), 'total')->default(0)->rules(['required', 'numeric', 'min:0']),
            Text::make(__('Notes'), 'description'),
        ];
    }

    public function fieldsForDetail(Request $request)
    {
        return [
            Tabs::make(__('Expense'), [
                __('Summary')      => [
                    BelongsTo::make(__('Provider'), 'provider', Provider::class),
                    Date::make(__('Invoice at'), 'emitted_at')->format('dddd D, MMMM YYYY'),
                    Text::make(__("Invoice number"), 'sequence_value'),
                    Number::make(__('Sub-Total'), 'subtotal')->displayUsing(fn($value) => number_format($value)),
                    Number::make(__('Discounts'), 'discounts')->displayUsing(fn($value) => number_format($value)),
                    Number::make(__('Taxes'), 'taxes')->displayUsing(fn($value) => number_format($value)),
                    Number::make(__('Total'), 'total')->displayUsing(fn($value) => number_format($value)),
                ],
                __('Header')       => [
                    // emitted_at
                    Date::make(__('Invoice at'), 'emitted_at')->format('dddd D, MMMM YYYY'),
                    Date::make(__('Expire at'), 'expire_at')->rules(['required', 'date'])->format('dddd D, MMMM YYYY'),
                    Text::make(__(strtolower($request->user()->team->country)."_tax_sequence_fiscal"), 'sequence_number'),
                    Date::make(__(strtolower($request->user()->team->country)."_tax_sequence_fiscal_expiry"), 'sequence_expire_at'),
                ],
                __('Distribution') => [
                    HasMany::make('items', 'items', ExpenseTransaction::class),
                ],
            ])
                ->defaultSearch(true)
                ->withToolbar(),
        ];
    }

    public function fieldsForIndex(Request $request)
    {
        return [
            BelongsTo::make(__('Provider'), 'provider', Provider::class),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class),
            Date::make(__('Invoice at'), 'emitted_at')->format('dddd D, MMMM YYYY'),
            Text::make(__("Invoice number"), 'sequence_value'),
            Number::make(__('Total'), 'total')->displayUsing(fn($value) => number_format($value)),
        ];
    }

    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->onlyOnDetail(),
            BelongsTo::make(__('Provider'), 'provider', Provider::class),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class),
            BelongsTo::make(__('Catalog'), 'category', Catalog::class),
            BelongsTo::make(__('Sub-Catalog'), 'subcategory', SubCatalog::class),
            HasMany::make('items', 'items', BudgetItem::class),
        ];
    }
}
