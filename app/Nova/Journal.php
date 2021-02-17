<?php

namespace App\Nova;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;
use Techouse\IntlDateTime\IntlDateTime;
use Whitecube\NovaFlexibleContent\Flexible;

class Journal extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Document::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
    ];
    public static $globalSearchLink=false;
    public static $globallySearchable=false;
    public static $searchable=false;
    public static $displayInNavigation=false;

    public static function group()
    {
        return __('Finances');
    }

    public static function label()
    {
        return __('Journals');
    }

    public static function singularLabel()
    {
        return __('Journal');
    }

    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->onlyOnDetail(),
            new Panel(__('Invoice detail'), $this->getFieldsForHead($request)),
            new Panel(__('Invoice sequence'), $this->getFieldsForSequence($request))
        ];
    }



    private function getFieldsForHead(Request $request): array
    {
        return [
            Hidden::make('team_id')->onlyOnForms()->default($request->user()->team->id),
            IntlDateTime::make(__('Emitted at'), 'emitted_at')
                ->required()
                ->hideUserTimeZone(),
            // kind
            Select::make(__('Entry type'), 'kind')
                ->placeholder(__('Type of document'))
                ->options(function () {
                    return [
                        Document::KIND_CASH_BILL      => __('Cash Bill'),
                        Document::KIND_CREDIT_INVOICE => __('Credit Invoice'),
                    ];
                })
                ->rules([
                    'required',
                    Rule::in(Document::KINDS),
                ]),
            BelongsTo::make(__('Provider'), 'provider', Doctor::class)
                ->withoutTrashed()
                ->display(fn($model) => "{$model->code} - {$model->name}"),

            Hidden::make(__('Currency'), 'currency')
                ->default($request->user()->team->currency)
                ->hideFromIndex(),
            Select::make(__('Currency'), 'exchange_currency')
                ->options(collect(config('ogo.currencies'))->mapWithKeys(function ($currency) {
                    return [$currency => __($currency)];
                }))
                ->default($request->user()->team->currency),
            Text::make(__('Notes'), 'description')->hideFromIndex(),
        ];
    }

    private function getFieldsForSequence(Request $request): array
    {
        return [
            Text::make(__('Sequence number'), 'sequence_number')
                ->hideFromIndex(),
            Text::make(__('Sequence value'), 'sequence_value')
                ->rules([
                    'nullable',
                    'string',
                    Rule::unique('documents', 'sequence_value')
                        ->where('team_id', $request->user()->team->id)
                        ->ignore('{{resourceId}}'),
                ]),
            IntlDateTime::make(__('Sequence expire at'), 'sequence_expire_at')
                ->rules([
                    'date',
                ])
                ->hideFromIndex()
                ->hideUserTimeZone(),
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
