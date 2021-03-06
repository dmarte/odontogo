<?php

namespace App\Nova;

use App\Models\Agreement;
use App\Models\Split;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class DoctorSplit extends Resource
{
    public static $model = Split::class;
    public static $title = 'name';
    public static $search = [];
    public static $displayInNavigation = false;
    public static $perPageOptions = [50, 100];
    public static $globallySearchable = false;
    public static $relatableSearchResults = 40;
    public static $showColumnBorders =true;
    public static $preventFormAbandonment=true;
    public static $searchable=false;
    public static $tableStyle = 'tight';

    public static function label()
    {
        return __('Agreements');
    }

    public static function singularLabel()
    {
        return __('Agreements');
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/'.Doctor::uriKey().'/'.$resource->model_id . '?tab=' . __('Agreement');
    }

    public function fields(Request $request)
    {
        return [
            Hidden::make('unit_action')->onlyOnForms()->default(Agreement::ACTION_DECREASE),
            Hidden::make('kind')->onlyOnForms()->default(Agreement::KIND_SPLIT),
            BelongsTo::make(__('Source'), 'source', Source::class)->nullable()->withoutTrashed()->searchable()->showCreateRelationButton(),
//            BelongsTo::make(__('Insurance'), 'insurance', Insurances::class)->nullable()->withoutTrashed()->searchable()->showCreateRelationButton(),
            MorphTo::make(__('Entity'), 'model')->hideWhenUpdating()->hideWhenCreating(),
            Hidden::make('model_type')->default((new \App\Models\Doctor())->getMorphClass())->onlyOnForms()->rules(['required','in:doctor']),
            Hidden::make('model_id')->default($request->get('viaResourceId'))->onlyOnForms()->rules(['required','numeric']),
            Number::make(__('Value'), 'unit_value')->min(0)->rules(['required','numeric','min:0']),
            Select::make(__('Agreement type'),'unit_type')
            ->rules([
                'required',
                'in:fix,percent'
            ])
            ->options([
                'fix'=> __('Agreement fix'),
                'percent'=> __('Agreement percent')
            ])
            ->default('percent')
            ->displayUsingLabels(),
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
