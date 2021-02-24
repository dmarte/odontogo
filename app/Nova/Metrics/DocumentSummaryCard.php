<?php

namespace App\Nova\Metrics;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class DocumentSummaryCard extends Value
{
    public $onlyOnDetail = true;
    public $width = '1/5';
    protected string $_field = 'amount';
    protected string $_label = 'Change amount';
    protected Model|null $_model = null;

    public function name()
    {
        return __($this->_label);
    }

    public function model(Model $resource) {
        $this->_model = $resource;
        return $this;
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
        dd($this->_model?->toArray(), $request->model()->toArray(), $request->route('resourceId'));
        return $this
            ->result((float) $this->_model?->getAttribute($this->_field))
            ->currency($this->_model?->currency)
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
        return 'document-change-amount';
    }
}
