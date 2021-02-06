<?php

namespace App\Nova;

use App\Models\Document;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Sequence extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Sequence::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'title',
    ];

    public static function group()
    {
     return __('Settings');
    }

    public static function label()
    {
        return __('Sequences');
    }

    public static function singularLabel()
    {
        return __('Sequence');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->onlyOnDetail(),
            Text::make(__('Title'), 'title')->rules(['required']),
            Text::make(__('Subtitle'), 'subtitle')->hideFromIndex(),
            Text::make(__('Prefix'), 'prefix')->onlyOnForms(),
            Text::make(__('Suffix'), 'suffix')->onlyOnForms(),
            Text::make(__('Next sequence'), function () {
                return $this->next_formatted;
            })
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->hideFromIndex(),
            Text::make(__('Current sequence'), function () {
                return $this->current_formatted;
            })
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            Number::make(__('Sequence length'), 'length')
                ->min(1)
                ->default(fn() => 8)
                ->rules([
                    'required',
                    'numeric',
                    'min:1',
                ])
                ->hideFromIndex(),
            Number::make(__('Sequence counter'), 'counter')
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            Number::make(__('Sequence initial counter'), 'initial_counter')
                ->min(1)
                ->hideWhenUpdating()
                ->hideFromIndex()
                ->default(1)
                ->creationRules([
                    'required',
                    'numeric',
                    'min:1',
                ]),
            Number::make(__('Sequence maximum'), 'maximum')
                ->min(1)
                ->rules([
                    'required',
                    'numeric',
                ])
                ->default(1000),

            Date::make(__('Expire date'), 'expire_at')
                ->nullable()
                ->rules([
                    'nullable',
                    'date',
                    'after_or_equal:today',
                ])
                ->format('LL'),
            BooleanGroup::make(__('Document types'), 'types')
                ->options(function () {
                    return collect(Document::KINDS)
                        ->mapWithKeys(fn($kind) => [$kind => __("document.{$kind}")])
                        ->toArray();
                })
                ->help(__('Indicate for which documents this this sequence will be used.'))
                ->rules([
                    'required',
                ])
                ->hideFalseValues(),
            BelongsTo::make(__('Team'), 'team', Team::class)
                ->default(fn(NovaRequest $request) => $request->user()->member->team_id)
                ->hideFromIndex(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
