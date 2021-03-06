<?php

namespace App\Nova;

use App\Models\Document;
use App\Nova\Actions\DocumentPrintAction;
use App\Nova\Actions\SendBudgetByEmailAction;
use App\Nova\Flexible\Presets\DocumentItemPreset;
use Eminiarts\Tabs\Tabs;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaButton\Button;
use Techouse\IntlDateTime\IntlDateTime;
use Titasgailius\SearchRelations\SearchesRelations;
use Whitecube\NovaFlexibleContent\Flexible;

class Budget extends Resource
{
    use SearchesRelations;

    public static $model = \App\Models\Budget::class;
    public static $preventFormAbandonment = true;
    public static $with = ['receiver', 'provider'];
    public static $title = 'code';
    public static $search = [
        'code',
        'sequence_value',
        'sequence_number'
    ];
    public static $globalSearchRelations = [
        'provider' => ['name', 'code'],
        'receiver' => ['name', 'code'],
    ];
    public static $searchRelations = [
        'provider' => ['name', 'code'],
        'receiver' => ['name', 'code'],
    ];

    public static $priority = 1;

    public static function softDeletes()
    {
        return false;
    }

    public function title()
    {
        return join(' - ', [
            $this->title,
            $this->provider?->name
        ]);
    }

    public function subtitle()
    {
        return __('Patient').':'.$this->receiver->name;
    }

    public static function group()
    {
        return __('Branch');
    }

    public static function label()
    {
        return __('Budgets');
    }

    public static function singularLabel()
    {
        return __('Budget');
    }

    public function fieldsForUpdate(NovaRequest $request) {
        return $this->fieldsForCreate($request);
    }

    public function fieldsForCreate(NovaRequest $request)
    {
        return [
            Hidden::make('team_id')->default($request->user()->team->id),
            Hidden::make('kind')->default(Document::KIND_INVOICE_BUDGET),
            Hidden::make('currency')->default($request->user()->team->currency),
            Hidden::make('exchange_currency')->default($request->user()->team->currency),
            Hidden::make('exchange_rate')->default(1),
            Hidden::make('author_user_id')->default($request->user()->id),
            Hidden::make('sequence_id')->default($request->user()->team->budgetSequence->id),
            IntlDateTime::make(__('Emitted at'), 'emitted_at')
                ->required()
                ->rules(['required', 'date'])
                ->hideUserTimeZone()
                ->default(now()->format('Y-m-d')),
            IntlDateTime::make(__('Expire at'), 'expire_at')
                ->required()
                ->rules(['required', 'date'])
                ->hideUserTimeZone()
                ->default(now()->add('days', 30)->format('Y-m-d')),
            BelongsTo::make(__('Doctor'), 'provider', Doctor::class)
                ->withoutTrashed()
//                ->searchable()
                ->withSubtitles()
                ->showCreateRelationButton()
                ->rules(['required', 'numeric']),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class)
                ->withoutTrashed()
                ->searchable()
                ->withSubtitles()
                ->showCreateRelationButton()
                ->rules(['required', 'numeric']),
            Text::make(__('Notes'), 'description')->nullable(),
            Flexible::make(__('Procedures'), 'procedures')
                ->preset(DocumentItemPreset::class, ['model' => $this->model()])
                ->rules([
                    'required',
                    'array',
                    'min:1',
                ]),
        ];
    }

    public function fieldsForDetail(Request $request)
    {
        return [
            Heading::make(__('Summary')),
                Text::make(__('Budget code'), 'code'),
                Number::make(__('Discounts'), 'discounts')->displayUsing(fn($value) => number_format($value)),
                Number::make(__('Subtotal'), 'subtotal')->displayUsing(fn($value) => number_format($value)),
                Number::make(__('Taxes'), 'taxes')->displayUsing(fn($value) => number_format($value)),
                Currency::make(__('Total'), 'total')->locale('en-US')->currency($this->resource?->currency),
            Heading::make(__('Budget detail')),
                IntlDateTime::make(__('Emitted at'), 'emitted_at')->hideUserTimeZone()->dateFormat('DD/MM/YYYY'),
                IntlDateTime::make(__('Expire at'), 'expire_at')->hideUserTimeZone()->dateFormat('DD/MM/YYYY'),
                BelongsTo::make(__('Doctor'), 'provider', Doctor::class)->withoutTrashed()->withSubtitles(),
                BelongsTo::make(__('Patient'), 'receiver', Patient::class)->withoutTrashed()->withSubtitles()->searchable(),
                BelongsTo::make(__('Created by'), 'author', User::class)->withoutTrashed()->withSubtitles(),
            Heading::make(__('Administrative area')),
                Select::make(__('Document type'), 'kind')->options(__('document'))->displayUsingLabels(),
                Text::make(__('Budget code'), 'code'),
                Number::make(__('Budget number'), 'sequence_number'),
                Text::make(__('Budget sequence'), 'sequence_value'),
                BelongsTo::make(__('Sequence reference'), 'sequence', Sequence::class)->withoutTrashed()->withSubtitles(),
                BelongsTo::make(__('Created by'), 'author', User::class),
            HasMany::make(__('Procedures'), 'items', BudgetItem::class),
        ];
    }

    public function fieldsForIndex()
    {
        return [
            Text::make(__('Budget sequence'), 'sequence_value'),
            IntlDateTime::make(__('Emitted at'), 'emitted_at')->hideUserTimeZone()->dateFormat('DD/MM/YYYY'),
            BelongsTo::make(__('Doctor'), 'provider', Doctor::class)
                ->viewable(false)
                ->withoutTrashed()
                ->searchable()
                ->withSubtitles()
                ->showCreateRelationButton(),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class)
                ->viewable(false)
                ->withoutTrashed()
                ->searchable()
                ->withSubtitles(),
            Number::make(__('Discounts'), 'discounts')->displayUsing(fn($value) => number_format($value)),
            Number::make(__('Total'), 'total')->displayUsing(fn($value) => number_format($value)),
        ];
    }

    public function fields(Request $request)
    {
        return [
            BelongsTo::make(__('Doctor'), 'provider', Doctor::class)
                ->viewable(false)
                ->withoutTrashed()
                ->searchable()
                ->withSubtitles()
                ->showCreateRelationButton(),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class)
                ->viewable(false)
                ->withoutTrashed()
                ->searchable()
                ->withSubtitles(),
            HasMany::make(__('Procedures'), 'items', BudgetItem::class),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new DocumentPrintAction())
                ->showOnDetail()
                ->withoutConfirmation()
                ->showOnTableRow(),
            (new SendBudgetByEmailAction())
                ->showOnDetail()
                ->showOnTableRow()
                ->confirmButtonText(__('Send budget'))
            ,
        ];
    }
}
