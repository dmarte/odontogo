<?php

namespace App\Nova;

use App\Models\Document;
use App\Nova\Flexible\Presets\DocumentItemPreset;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Techouse\IntlDateTime\IntlDateTime;
use Whitecube\NovaFlexibleContent\Flexible;

class Budget extends Resource
{
    public static $model = \App\Models\Budget::class;
    public static $preventFormAbandonment = true;
    public static $with = ['receiver', 'provider'];
    public static $title = 'title';

    public static $search = [
        'code',
        'title',
        'sequence_value',
    ];

    public static $priority = 1;

    public function title()
    {
        return "{$this->title} - {$this->provider->name}";
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
//                ->searchable()
                ->withSubtitles()
                ->showCreateRelationButton()
                ->rules(['required', 'numeric']),
            Text::make(__('Notes'), 'description')->nullable(),
            Flexible::make(__('Procedures'), 'procedures')->preset(DocumentItemPreset::class, ['model' => $this->model()]),
        ];
    }

    public function fieldsForDetail(Request $request) {
        return [
            Heading::make(__('Administrative area')),
            Number::make(__('Budget number'), 'sequence_number'),
            Text::make(__('Budget Id'),'sequence_value'),
            BelongsTo::make(__('Sequence'), 'sequence', Sequence::class)->withoutTrashed()->withSubtitles(),

            Heading::make(__('Budget detail')),
            IntlDateTime::make(__('Emitted at'), 'emitted_at')->hideUserTimeZone()->dateFormat('DD/MM/YYYY'),
            IntlDateTime::make(__('Expire at'), 'expire_at')->hideUserTimeZone()->dateFormat('DD/MM/YYYY'),
            BelongsTo::make(__('Doctor'), 'provider', Doctor::class)->withoutTrashed()->withSubtitles(),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class)->withoutTrashed()->withSubtitles(),
            BelongsTo::make(__('Created by'), 'author', User::class)->withoutTrashed()->withSubtitles(),

            Heading::make(__('Summary')),
            Number::make(__('Discounts'), 'discounts'),
            Number::make(__('Subtotal'), 'subtotal'),
            Number::make(__('Taxes'),'taxes'),
            Currency::make(__('Total'),'total')->currency($this->resource?->currency),
            HasMany::make(__('Procedures'), 'items', BudgetItem::class),
        ];
    }

    public function fields(Request $request)
    {
        return [
            Text::make(__('Code'),'code')->sortable(),
            Text::make(__('Sequence'), 'sequence_value')->sortable(),
            BelongsTo::make(__('Doctor'), 'provider', Doctor::class)
                ->withoutTrashed()
                ->searchable()
                ->withSubtitles(),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class)
                ->withoutTrashed()
                ->searchable()
                ->withSubtitles(),
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
        return [];
    }
}
