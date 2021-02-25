<?php

namespace App\Nova\Metrics;

use App\Models\Receipt;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class ReceiptsIncomeByPeriod extends Value
{
    public $width = '1/2';

    public function name()
    {
        return __('Income by period');
    }

    public function calculate(NovaRequest $request)
    {
        return $this
            ->sum($request, Receipt::class, 'amount_paid', 'paid_at')
            ->currency()
            ->format('0,0[.]00')
            ->allowZeroResult();
    }

    public function ranges()
    {
        return [
            30 => __('30 Days'),
            60 => __('60 Days'),
            365 => __('365 Days'),
            'TODAY' => __('Today'),
            'MTD' => __('Month To Date'),
            'QTD' => __('Quarter To Date'),
            'YTD' => __('Year To Date'),
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
         return now()->addMinutes(5);
    }

    public function uriKey()
    {
        return 'receipts-income-by-period';
    }
}
