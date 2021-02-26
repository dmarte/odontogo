<?php

namespace App\Nova\Metrics;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class DocumentSummaryCard extends Value
{
    public $onlyOnDetail = true;
    public $width = '1/3';
    protected string $_field = 'amount';
    protected string $_label = 'Change amount';

    public function name()
    {
        return __($this->_label);
    }

    public function label(string $label) {
        $this->_label = $label;
        return $this;
    }

    public function field(string $field) {
        $this->_field = $field;
        return $this;
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        /** @var \App\Models\Document $model */
        $model = $request->findModelQuery($request->route('resourceId'))->first();

        return $this
            ->result((float) $model?->getAttribute($this->_field))
            ->dollars()
            ->format('0,0[.]00')
            ->allowZeroResult();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [];
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
        return $this->_field;
    }
}
