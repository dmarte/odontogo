<?php

namespace App\Nova;

use App\Nova\Actions\UserInvitationAction;
use App\UploadAvatar;
use Dniccum\PhoneNumber\PhoneNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\Country;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Timezone;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;

class Team extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Team::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    public static $searchable = false;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->whereHas('members', function (Builder $builder) use ($request) {
            $builder->where('user_id', $request->user()->id);
        });
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query->whereHas('members', function (Builder $builder) use ($request) {
            $builder->where('user_id', $request->user()->id);
        });
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return $query->whereHas('members', function (Builder $builder) use ($request) {
            $builder->where('user_id', $request->user()->id);
        });
    }


    public static function group()
    {
        return __('Settings');
    }

    public static function label()
    {
        return __('Teams');
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
        $currency = $this->model()?->currency ?? $this->resource?->currency ?? $request->user()->team->currency ?? config('nova.currency');

        return [
            ID::make(__('ID'), 'id')->hideFromIndex(),
            Avatar::make(__('Logo'), 'avatar_path')
                ->store(new UploadAvatar)
            ->disableDownload()
            ->squared(),
            Text::make(__('Name'), 'name'),
            Timezone::make(__('Time zone'), 'time_zone'),
            Country::make(__('Country'), 'country'),
            Select::make(__('Currency'), 'currency')
                ->rules(['required', 'size:3'])
                ->options(collect(config('ogo.currencies'))->mapWithKeys(function ($currency) {
                    return [$currency => __($currency)];
                }))
                ->default($currency),
            PhoneNumber::make(__('Primary phone'), 'phone_primary')
                ->disableValidation(),
            PhoneNumber::make(__('Secondary phone'), 'phone_secondary')
                ->hideFromIndex()
                ->disableValidation(),
            Text::make(__('Address'), 'address_line_1')
                ->hideFromIndex(),
            Text::make(__('Email'), 'email')
                ->rules([
                    'nullable',
                    'email',
                ]),
            Hidden::make('user_id')
                ->default($request->user()->id)
                ->showOnCreating(),
            Hidden::make('user_id')
                ->showOnUpdating(),
            HasMany::make(__('Members'), 'members', Member::class),
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
        return [
            (new UserInvitationAction($this->resource))
                ->confirmButtonText(__('Invite'))
                ->cancelButtonText(__('Cancel'))
                ->onlyOnDetail()
                ->showOnTableRow()
                ->canSee(function ($request) {
                    if ($request instanceof ActionRequest) {
                        return true;
                    }

                    if (!$this->resource instanceof \App\Models\Team) {
                        return false;
                    }

                    return $request->user()->can('invite', $this->resource);
                }),
        ];
    }
}
