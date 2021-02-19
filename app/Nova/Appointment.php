<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Techouse\IntlDateTime\IntlDateTime;
use Titasgailius\SearchRelations\SearchesRelations;

class Appointment extends Resource
{
    use SearchesRelations;

    public static $priority = 0;
    public static $model = \App\Models\Appointment::class;
    public static $displayInNavigation = false;
    public static $searchRelations = [
        'doctor'  => ['name'],
        'patient' => ['name'],
    ];
    public static $search = [
        'title',
    ];

    public function title()
    {
        return "{$this->patient->name} - {$this->title}";
    }

    public function subtitle()
    {
        return $this->at->format('d/m/Y');
    }

    public static function group()
    {
        return __('Branch');
    }

    public static function label()
    {
        return __('Appointments');
    }

    public static function singularLabel()
    {
        return __('Appointment');
    }

    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            IntlDateTime::make(__('Appointment date'), 'at')
                ->hideUserTimeZone()
                ->withTimeShort()
                ->withShortcutButtons()
                ->userTimeZone($request->user()->time_zone)
                ->maxDate(now()->addYears(2))
                ->minDate(now())
                ->pickerFormat('YYYY-MM-DD HH:mm Z')
                ->pickerDisplayFormat('D, d M Y')
                ->rules([
                    'required',
                    'date',
                    'min:today',
                ]),
            BelongsTo::make(__('Doctor'), 'doctor', Doctor::class)
                ->required()
                ->rules(['required', 'numeric'])
                ->showCreateRelationButton()
                ->withoutTrashed(),
            BelongsTo::make(__('Patient'), 'patient', Patient::class)
                ->required()
                ->rules(['required', 'numeric'])
                ->showCreateRelationButton()
                ->withoutTrashed(),
            BelongsTo::make(__('Source'), 'source', Source::class)
                ->required()
                ->rules(['required', 'numeric'])
                ->showCreateRelationButton()
                ->withoutTrashed(),

            BelongsTo::make(__('Budget'), 'budget', Budget::class)
                ->nullable()
                ->searchable()
                ->withoutTrashed(),
            Text::make(__('Title'), 'title')->default(__('New appointment'))->nullable(),
            Textarea::make(__('Notes'), 'description')->nullable(),
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
