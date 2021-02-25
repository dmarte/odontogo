<?php

namespace App\Nova;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Wallet extends Resource
{
    const KIND = Attribute::KIND_WALLET;

    public static $model = \App\Models\Wallet::class;


    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */

    public static $tableStyle = 'tight';
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
    ];
    public static $priority = 3;
    public static $globallySearchable = false;

    public function title()
    {
        return "{$this->code} - {$this->name}";
    }

    public static function group()
    {
        return __('Branch');
    }

    public static function label()
    {
        return __('Wallets');
    }

    public static function singularLabel()
    {
        return __('Wallet');
    }


    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable()->onlyOnDetail(),
            Text::make(__('Code'), 'code')
                ->rules([
                    'required',
                    Rule::unique('attributes', 'code')
                        ->where('team_id', $request->user()->team->id)
                        ->where('kind', self::KIND),
                ])
                ->sortable(),
            Text::make(__('Name'), 'name')
                ->creationRules([
                    'required',
                    'min:4',
                ]),
            Textarea::make(__('validation.attributes.description'), 'description'),
            Hidden::make(__('validation.attributes.team_id'), 'team_id')
                ->default(fn(NovaRequest $request) => $request->user()->team_id)
                ->showOnCreating(),
            BelongsTo::make(__('Wallet Parent'), 'parent', static::class)
                ->nullable()
                ->showCreateRelationButton()
                ->withoutTrashed()
                ->display(function ($model) {
                    return "{$model->code} - {$model->name}";
                }),
            Number::make(__('Debit'), 'amount_credit')->readonly()->hideWhenUpdating()->hideWhenCreating(),
            Number::make(__('Credit'), 'amount_debit')->readonly()->hideWhenCreating()->hideWhenUpdating(),
            Boolean::make(__('Enabled'), 'enabled')
                ->default(fn() => true)
                ->hideWhenCreating()
                ->hideFromIndex(),
            Hidden::make('enabled')
                ->default(fn() => 1)
                ->showOnCreating(),
            Hidden::make('kind')
                ->onlyOnForms()
                ->default(fn() => self::KIND),
        ];
    }
}
