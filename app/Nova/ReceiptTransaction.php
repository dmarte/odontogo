<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class ReceiptTransaction extends Resource
{
    public static $model = \App\Models\ReceiptTransaction::class;

    public static $title = 'id';
    public static $search = [];

    public function fields(Request $request)
    {
        return [
            Date::make(__('Paid at'), 'emitted_at')->format('D MMM YYYY'),
            BelongsTo::make(__('Receipt'), 'document', Receipt::class),
            BelongsTo::make(__('Doctor'), 'provider', Doctor::class),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class),
            Number::make(__('Payment'), 'total')->displayUsing(fn($value) => number_format($value)),
        ];
    }

    public static function authorizedToCreate(Request $request)
    {
        $resource = explode('/', $request->route('view'))[1] ?? $request->get('viaResource');

        return ($resource === Receipt::uriKey());
    }
}
