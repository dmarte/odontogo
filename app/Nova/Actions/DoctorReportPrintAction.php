<?php

namespace App\Nova\Actions;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Hidden;
use Techouse\IntlDateTime\IntlDateTime;

class DoctorReportPrintAction extends Action
{
    use InteractsWithQueue, Queueable;


    public function name()
    {
        return __('Doctor report');
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $from = now()->setTimestamp(strtotime($fields->from))->format('Y-m-d');
        $to = now()->setTimestamp(strtotime($fields->to))->format('Y-m-d');
        return Action::openInNewTab(route('print.doctor_report', $fields->resource) . "?from={$from}&to={$to}");
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Hidden::make('resource')->default(request()->get('resourceId')),
            IntlDateTime::make(__('Date from'), 'from')
                ->rules([
                    'required',
                    'before_or_equal:'.now()->format('Y-m-d'),
                ])
                ->userTimeZone('UTC')
                ->hideUserTimeZone()
                ->default(now()->firstOfMonth(Carbon::MONDAY)->format('Y-m-d')),
            IntlDateTime::make(__('Date to'), 'to')
                ->rules([
                    'required',
                    'before_or_equal:'.now()->format('Y-m-d'),
                ])
                ->userTimeZone('UTC')
                ->hideUserTimeZone()
                ->default(now()->format('Y-m-d')),
        ];
    }
}
