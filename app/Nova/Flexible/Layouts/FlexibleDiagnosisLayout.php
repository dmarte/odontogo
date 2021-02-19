<?php

namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class FlexibleDiagnosisLayout extends Layout
{
    /**
     * The layout's unique identifier
     *
     * @var string
     */
    protected $name = 'diagnosis';

    /**
     * The displayed title
     *
     * @var string
     */
    public function title()
    {
        return __('Diagnosis');
    }

    /**
     * Get the fields displayed by the layout.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make(__('Diagnosis'), 'diagnosis')
                ->searchable()
                ->options(function () {
                    return request()->user()->team->diagnosis->pluck('name', 'id');
                }),
            Number::make(__('Tooth number'), 'tooth_number')->help(__('Please indicate the tooth number affected by the selected procedure.')),
            Select::make(__('Tooth side'), 'tooth_side')
                ->options([
                    'mesial'     => __('Mesial'),
                    'distal'     => __('Distal'),
                    'vestibular' => __('Vestibular'),
                    'palatina'   => __('Palatina'),
                ]),
        ];
    }

}
