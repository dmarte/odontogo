<?php

namespace App\Nova\Metrics;

use App\Models\Contact;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class NewPatientsMetrics extends Value
{
    public function name()
    {
        return __('New patients');
    }

    /**
     * Calculate the value of the metric.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $query = Contact::where('kind', Contact::KIND_PATIENT)
            ->where('team_id', $request->user()->member->team_id);

        return $this->count($request, $query, 'registered_at')
            ->allowZeroResult();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30      => __('30 Days'),
            60      => __('60 Days'),
            365     => __('365 Days'),
            'TODAY' => __('Today'),
            'MTD'   => __('Month To Date'),
            'QTD'   => __('Quarter To Date'),
            'YTD'   => __('Year To Date'),
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'new-patients-metrics';
    }
}
