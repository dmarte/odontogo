<?php

namespace App\Nova;

use App\Models\Item;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;

class PatientPayments extends Resource
{
    public static $with = ['doctor','receipt', 'author'];
    public static $model = Item::class;
    public static $title = 'id';
    public static $search = [
        'id',
    ];

    public function fields(Request $request)
    {
        return [
            Date::make(__('Paid at'), 'emitted_at')->format('D MMM YYYY'),
            BelongsTo::make(__('Receipt'), 'receipt', Receipt::class)->displayUsing(fn($model) => $model->code),
            BelongsTo::make(__('Doctor'), 'provider', Doctor::class),
            Number::make(__('Payment'), 'amount_paid')->displayUsing(fn($value) => number_format($value)),
            BelongsTo::make(__('Created by'), 'author', User::class)->withoutTrashed()->viewable(false),
        ];
    }

    public static function softDeletes()
    {
        return false;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToView(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToForceDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }
}
