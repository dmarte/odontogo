<?php

namespace App\Nova;

use App\Models\Agreement;
use App\Models\Vat;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class DoctorVat extends Resource
{
    public static $perPageViaRelationship = 100;
    public static $model = Vat::class;
    public static $title = 'id';
    public static $search = [];

    public static function label()
    {
        return __('Taxes');
    }

    public static function singularLabel()
    {
        return __('Tax');
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/'.Doctor::uriKey().'/'.$resource->model_id . '?tab=' . __('Agreement');
    }

    public function fields(Request $request)
    {
        return [
            Hidden::make('unit_action')->onlyOnForms()->default(Agreement::ACTION_DECREASE),
            Hidden::make('kind')->onlyOnForms()->default(Agreement::KIND_VAT),
            Text::make(__('Title'),'title'),
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

}
