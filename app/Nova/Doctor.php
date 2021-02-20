<?php

namespace App\Nova;

use App\Models\Contact;
use Dniccum\PhoneNumber\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Country;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Doctor extends Resource
{
    private const KIND = \App\Models\Doctor::KIND_DOCTOR;

    public static $model = \App\Models\Doctor::class;

    public static $title = 'name';

    public static $search = [
        'name',
    ];

    public static $tableStyle = 'tight';

    public static $showColumnBorders =true;

    public static $priority = 2;

    public function subtitle()
    {
        return $this->code;
    }

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
            Hidden::make('kind')->onlyOnForms()->default(self::KIND),
            Hidden::make('team_id')->showOnCreating()->default($request->user()->team->id),
            new Panel(__('Doctor information'), $this->getFiscalFields($request->user())),
            new Panel(__('Address'), $this->getAddressFields($request->user())),
//            new Panel(__('Credit information'), $this->getCreditFields($request->user())),
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

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->where('team_id', $request->user()->team->id)
            ->where('kind', self::KIND);

    }

    public static function scoutQuery(NovaRequest $request, $query)
    {
        return $query
            ->where('team_id', $request->user()->team->id)
            ->where('kind', self::KIND);
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return $query
            ->where('team_id', $request->user()->team->id)
            ->where('kind', self::KIND);
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query
            ->where('team_id', $request->user()->team->id)
            ->where('kind', self::KIND);
    }

    private function getFiscalFields(\App\Models\User $user): array
    {
        $country = strtolower($user->country);
        $team = $user->team;

        return [
            Stack::make(__('Doctor'), [
                Text::make(__("Name"), 'tax_payer_name')->hideWhenUpdating(),
                Text::make(__("{$country}_identification_number"), 'tax_payer_number')->hideWhenCreating()
            ]),
            Text::make(__("Name"), 'tax_payer_name')
                ->help(__('The name as shown on the identification card.'))
                ->rules(['required'])
                ->hideFromIndex(),
            Text::make(__("{$country}_identification_number"), 'tax_payer_number')
                ->creationRules(['required', Rule::unique('contacts', 'tax_payer_number')->where('team_id', $team->id)])
                ->hideFromIndex()
                ->rules(['required_with:tax_payer_name']),
            BelongsTo::make(__('Catalog'), 'category', Catalog::class)->onlyOnDetail(),
            BelongsTo::make(__('Sub-Catalog'), 'subcategory', SubCatalog::class)->onlyOnDetail(),
            BelongsTo::make(__('Career'), 'career', Careers::class)
                ->rules(['required', 'numeric'])
                ->hideFromIndex()
                ->withoutTrashed()
                ->showCreateRelationButton(),
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

    public static function label()
    {
        return __('Doctors');
    }

    public static function singularLabel()
    {
        return __('Doctor');
    }

    public static function createButtonLabel()
    {
        return __('New resource', ['resource'=>__('Doctor')]);
    }

    public static function updateButtonLabel()
    {
        return __('Save');
    }
    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
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
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
