<?php

namespace App\Nova\Actions;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Hidden;

class DoctorReportPrintAction extends Action
{
    use InteractsWithQueue, Queueable;


    public function name()
    {
        return __('Doctor report');
    }

    public function handle(ActionFields $fields, Collection $models)
    {

    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
//            Hidden::make('id')->default(request()->get('resourceId')),
            Date::make(__('Date from'), 'from')
                ->rules([
                    'required',
                    'date',
                    'before_or_equal:'.now()->format('Y-m-d'),
                ]),
            Date::make(__('Date to'), 'to')
                ->rules([
                    'required',
                    'date',
                    'before_or_equal:'.now()->format('Y-m-d'),
                ]),
        ];
    }
}
