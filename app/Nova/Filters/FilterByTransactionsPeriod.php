<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class FilterByTransactionsPeriod extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public function name()
    {
        return __('Filter by period');
    }

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        switch($value) {
            case 'WEEK':
                $query
                    ->whereBetween('emitted_at', [
                        now()->startOfWeek()->format('Y-m-d'),
                        now()->endOfWeek()->format('Y-m-d'),
                    ]);
                break;
            case 'LAST_WEEK':
                /** @var \Carbon\Carbon $start */
                $start = now()->startOfWeek()->sub('days',7);
                $query
                    ->whereBetween('emitted_at', [
                        $start->startOfWeek()->format('Y-m-d'),
                        $start->endOfWeek()->format('Y-m-d'),
                    ]);
                break;
            case 'MONTH':
                /** @var \Carbon\Carbon $start */
                $start = now()->startOfMonth();
                $query
                    ->whereBetween('emitted_at', [
                        $start->format('Y-m-d'),
                        $start->endOfMonth()->format('Y-m-d'),
                    ]);
                break;
            case 'LAST_MONTH':
                /** @var \Carbon\Carbon $start */
                $start = now()->startOfMonth()->sub('month', 1);
                $query
                    ->whereBetween('emitted_at', [
                        $start->format('Y-m-d'),
                        $start->endOfMonth()->format('Y-m-d'),
                    ]);
                break;
            case 'YEAR':
                /** @var \Carbon\Carbon $start */
                $start = now()->startOfYear();
                $query
                    ->whereBetween('emitted_at', [
                        $start->format('Y-m-d'),
                        $start->endOfYear()->format('Y-m-d'),
                    ]);
                break;
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            __('This week') => 'WEEK',
            __('Last week') => 'LAST_WEEK',
            __('This month') => 'MONTH',
            __('Last month') => 'LAST_MONTH',
//            __('Las 3 months') => 'TREE_MONTHS',
            __('This year') => 'YEAR',
        ];
    }
}
