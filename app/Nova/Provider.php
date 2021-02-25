<?php

namespace App\Nova;

use App\Models\Contact;
use Dniccum\PhoneNumber\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Country;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

class Provider extends Resource
{
    public static $model = \App\Models\Provider::class;
    public static $displayInNavigation = true;
    public static $globallySearchable = false;

    public static $title = 'name';

    public static $search = [
        'name',
    ];

    public static $tableStyle = 'tight';

    public static $showColumnBorders = true;

    public static function group()
    {
        return __('Branch');
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
            Text::make(__('Id'), 'counter')->onlyOnDetail(),
            Text::make(__('Code'), 'code')->hideWhenCreating()->hideWhenUpdating(),
            Hidden::make('kind')->onlyOnForms()->default(Contact::KIND_PROVIDER),
            Hidden::make('team_id')->showOnCreating()->default($request->user()->team->id),
            new Panel(__('Fiscal Information'), $this->getFiscalFields($request->user())),
            new Panel(__('Contact information'), $this->getContactFields($request->user())),
            new Panel(__('Address'), $this->getAddressFields($request->user())),
            new Panel(__('Credit information'), $this->getCreditFields($request->user())),
        ];
    }

    private function getCreditFields(\App\Models\User $user): array
    {
        return [
            Number::make(__('Amount approved'), 'credit_value')->min(0)->default(0),
            Number::make(__('Credit days'), 'credit_days')->min(1)->max(365)->default(1),
        ];
    }

    private function getContactFields(): array
    {
        return [
            Text::make(__('Provider name'), 'name')
                ->nullable()
                ->help(__('The person we can ask for.')),
            Text::make(__('Job title'), 'title')->hideFromIndex(),
            Select::make(__('Gender'), 'gender')
                ->hideFromIndex()
                ->required()
                ->displayUsingLabels()
                ->default(Contact::GENDER_NONE)
                ->options(function () {
                    return [
                        Contact::GENDER_NONE   => '-',
                        Contact::GENDER_MALE   => __('Male'),
                        Contact::GENDER_FEMALE => 'Female',
                    ];
                }),
            PhoneNumber::make(__('Primary phone'), 'phone_primary')
                ->disableValidation()
                ->required(),
            PhoneNumber::make(__('Secondary phone'), 'phone_secondary')->nullable()
                ->disableValidation()
                ->onlyOnForms()
                ->hideFromIndex(),
            Text::make(__('Email'), 'email_primary')->rules(['email'])->hideFromIndex(),
        ];
    }

    private function getAddressFields(\App\Models\User $user): array
    {
        return [
            Country::make(__('Country'), 'country_code')
                ->default(fn() => $user->country)
                ->displayUsingLabels()
                ->hideFromIndex()
                ->rules([
                    'required',
                ]),
            Text::make(__('City'), 'city_name')->hideFromIndex(),
            Text::make(__('Address line 1'), 'address_line_1')->hideFromIndex(),
            Text::make(__('Address line 2'), 'address_line_2')->hideFromIndex(),
        ];
    }

    private function getFiscalFields(\App\Models\User $user): array
    {
        $country = strtolower($user->country);
        $team = $user->team;

        return [
            Stack::make(__('Provider'), [
                Text::make(__("{$country}_tax_payer_name"), 'tax_payer_name')->hideWhenUpdating(),
                Text::make(__("{$country}_tax_payer_number"), 'tax_payer_number')->hideWhenCreating(),
            ]),
            Text::make(__("{$country}_tax_payer_name"), 'tax_payer_name')
                ->rules(['required'])
                ->hideFromIndex(),
            Text::make(__("{$country}_tax_payer_number"), 'tax_payer_number')
                ->creationRules(['required', Rule::unique('contacts', 'tax_payer_number')->where('team_id', $team->id)])
                ->hideFromIndex()
                ->rules(['required_with:tax_payer_name']),
            BelongsTo::make(__('Catalog'), 'category', Catalog::class)
                ->placeholder(__('Select the catalog to be matched for this provider'))
                ->rules(['required', 'numeric'])
                ->hideFromIndex()
                ->withoutTrashed()
                ->showCreateRelationButton()
                ->default(function(){
                    return \App\Models\Catalog::expenses()->first()->id;
                })
                ->display(function ($model) {
                    return "{$model->code} - {$model->name}";
                }),
        ];
    }

    public static function label()
    {
        return __('Providers');
    }

    public static function singularLabel()
    {
        return __('Provider');
    }

    public static function createButtonLabel()
    {
        return __('Create');
    }

    public static function updateButtonLabel()
    {
        return __('Update');
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
