<?php

namespace App\Nova;

use App\Models\Contact;
use App\Nova\Metrics\NewPatientsMetrics;
use App\UploadAvatar;
use Dniccum\PhoneNumber\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Country;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Patient extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Contact::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    public static $priority = 1;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
        'code',
    ];

    public static function group()
    {
        return __('Branch');
    }

    public static function label()
    {
        return __('Patients');
    }

    public static function singularLabel()
    {
        return __('Patient');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('team_id', $request->user()->member->team_id);
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return $query->where('team_id', $request->user()->member->team_id);
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query->where('team_id', $request->user()->member->team_id);
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
        $country = strtolower($request->user()->country);
        /* @var $member \App\Models\Member */
        $member = $request->user()->member;

        return [
            Text::make(__('ID'), 'counter')->sortable()->hideWhenUpdating()->hideWhenCreating(),

            Text::make(__('Code'), 'code')->hideWhenCreating()->hideWhenUpdating(),

            // ------------ [ Personal information ]

            Heading::make(__('Personal Information')),

            Avatar::make(__('Avatar'), 'avatar_path')
                ->hideWhenCreating()
                ->squared()
                ->store(new UploadAvatar)
                ->disableDownload(),

            Hidden::make('avatar_disk')->onlyOnForms()->default(fn() => config('filesystems.default')),

            Text::make(__('Full name'), 'name')
                ->rules([
                    'required',
                ])
                ->onlyOnForms(),
            Stack::make(__('Patient'), [
                Line::make(__('Name'), 'name')->asHeading(),
                Date::make(__('Registered at'), 'registered_at')->format('LL'),
            ]),
            Select::make(__('Gender'), 'gender')
                ->default(fn() => 'male')
                ->options([
                    'male'   => __('Male'),
                    'female' => __('Female'),
                ])
                ->displayUsingLabels()
                ->hideFromIndex()
                ->rules([
                    'required',
                    'in:male,female',
                ]),
            Date::make(__('Birthday'), 'birthday')
                ->rules([
                    'nullable',
                    'date',
                ])
                ->hideFromIndex(),
            Text::make(__("{$country}_identification_number"), 'identification_number')
                ->hideFromIndex(),

            Text::make(__('Job company'), 'company')
                ->hideFromIndex(),
            Text::make(__('Job title'), 'title')
                ->hideFromIndex(),


            Hidden::make('currency_code')
                ->default(fn() => $request->user()->currency)
                ->rules([
                    'required',
                    'size:3',
                ])
                ->onlyOnForms(),
            Hidden::make('kind')
                ->default(fn() => Contact::KIND_PATIENT)
                ->onlyOnForms()
                ->rules([
                    'required',
                ]),
            // ----------- [ Contact ]
            Heading::make(__('Contact information')),
            PhoneNumber::make(__('Primary phone'), 'phone_primary')
                ->disableValidation()
                ->useMaskPlaceholder()
                ->onlyOnForms()
                ->country($request->user()->country)
            ,
            PhoneNumber::make(__('Secondary phone'), 'phone_secondary')
                ->hideWhenCreating()
                ->hideFromIndex()
                ->hideFromDetail()
                ->disableValidation()
                ->useMaskPlaceholder(),
            Text::make(__('Primary email'), 'email_primary')
                ->rules([
                    'nullable',
                    'email',
                    Rule::unique('contacts', 'email_primary')
                        ->whereNull('deleted_at')
                        ->where('team_id', $request->user()->team_id),
                ])
                ->hideFromIndex(),
            Text::make(__('Secondary email'), 'email_secondary')
                ->nullable()
                ->rules([
                    'nullable',
                    'email',
                ])
                ->hideWhenCreating()
                ->hideFromIndex(),
            // ----------- [ Adress ]
            Heading::make(__('Address')),
            Country::make(__('Country'), 'country_code')
                ->default(fn() => $request->user()->country)
                ->displayUsingLabels()
                ->hideFromIndex()
                ->rules([
                    'required',
                ]),
            Text::make(__('City'), 'city_name'),
            Text::make(__('Address'), 'address_line_1')->hideFromIndex(),

            // ---------- [ Insurance information ]

            Heading::make(__('Insurance information')),
            BelongsTo::make(__('Insurance company'), 'insurance', Insurances::class)
                ->withoutTrashed()
                ->nullable()
                ->hideFromIndex(),
            Text::make(__('Insurance number'), 'insurance_number')->hideFromIndex(),

            // ---------- [ Administrative Information ]

            Heading::make(__('Administrative information')),

            BelongsTo::make(__('Team'), 'team', Team::class)
                ->default(fn() => $request->user()->member->team_id)
                ->onlyOnDetail(),

            Hidden::make('team_id')
                ->default(fn() => $request->user()->member->team_id)
                ->onlyOnForms(),

            Date::make(__('Registered at'), 'registered_at')
                ->hideFromIndex()
                ->default(fn() => now()->format('Y-m-d'))
                ->withMeta([
                    'value' => now()->format('Y-m-d'),
                ])
                ->rules([
                    'required',
                    'date',
                ])
                ->format('LL'),

            BelongsTo::make(__('Source'), 'source', Source::class)
                ->withoutTrashed()
                ->help(__('how did you hear about us?'))
                ->rules([
                    'required',
                    'numeric',
                ]),
            BelongsTo::make(__('Category'), 'category', Category::class)
                ->nullable()
                ->withoutTrashed()
                ->hideWhenCreating()
                ->hideFromIndex(),
            BelongsTo::make(__('Subcategory'), 'subcategory', Category::class)
                ->nullable()
                ->withoutTrashed()
                ->hideWhenCreating()
                ->hideFromIndex(),

            // ---------- [ Tax information ]

            Heading::make(__('Tax information'))
                ->hideWhenCreating()
                ->hideFromIndex(),
            Text::make(__("{$country}_tax_payer_name"), 'tax_payer_name')
                ->hideWhenCreating()
                ->hideFromIndex(),
            Text::make(__("{$country}_tax_payer_number"), 'tax_payer_number')
                ->hideWhenCreating()
                ->hideFromIndex(),

            // ---------- [ Credit information ]

            Heading::make(__('Credit information'))->hideWhenCreating(),
            Hidden::make('credit_value')->default(0)->showOnCreating(),
            Hidden::make('credit_days')->default(0)->showOnCreating(),
            Number::make(__('Credit amount'), 'credit_value')
                ->default(0)
                ->hideWhenCreating()
                ->hideFromIndex()
//                ->canSeeWhen('modifyCredit', $this)
            ,
            Number::make(__('Credit days'), 'credit_days')
                ->default(0)
                ->hideWhenCreating()
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
        return [
            NewPatientsMetrics::make(),
        ];
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