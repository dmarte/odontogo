<?php

namespace App\Nova;

use App\Models\Item;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class DoctorTransactions extends Resource
{
    public static $with = ['patient', 'doctor.agreements'];
    public static $model = Item::class;
    public static $title = 'id';
    public static $displayInNavigation=false;
    public static $search = [];

    public function fields(Request $request)
    {
        return [
            Date::make(__('Paid at'), 'emitted_at')->format('D MMM YYYY'),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class),
            Text::make(__('Agreement'), function () {
                return $this->doctor->agreement($this->patient->source_attribute_id)->unit_representation;
            }),
            Number::make(__('Income'),'amount_paid'),
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
