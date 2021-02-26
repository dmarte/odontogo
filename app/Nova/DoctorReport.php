<?php

namespace App\Nova;

use App\Models\Item;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class DoctorReport extends Resource
{
    public static $globallySearchable = false;
    public static $searchable = false;
    public static $displayInNavigation = false;
    public static $relatableSearchResults = 100;
    public static $tableStyle = 'tight';
    public static $perPageOptions = [100];
    public static $model = Item::class;
    public static $title = 'id';
    public static $search = [];

    public function authorizedToView(Request $request)
    {
        return false;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToRestore(Request $request)
    {
        return false;
    }

    public function authorizedToForceDelete(Request $request)
    {
        return false;
    }

    public function authorizedToAdd(NovaRequest $request, $model)
    {
        return false;
    }

    public function authorizedToAttachAny(NovaRequest $request, $model)
    {
        return false;
    }

    public function authorizedToAttach(NovaRequest $request, $model)
    {
        return false;
    }

    public function authorizedToDetach(NovaRequest $request, $model, $relationship)
    {
        return false;
    }


    public function fields(Request $request)
    {
        return [
            Date::make(__('Emitted at'), 'emitted_at')->format('D MMM YYYY'),
            Text::make(__('Concept'), function() {
                if ($this->title) {
                    return $this->title;
                }

                return $this->source;
            }),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class)->viewable(false),
            Text::make(__('Agreement'), function () {
                $value = (float) $this->unit_value;
                if ($this->unit_type === 'fix') {
                    return number_format($value);
                }

                return "{$value}%";
            }),
            Text::make(__('Expense'), function () {
                if ($this->data['kind'] === \App\Models\Document::KIND_EXPENSE) {
                    return number_format((float) $this->total * -1);
                }
            }),
            Text::make(__('Income'), function () {
                if ($this->data['kind'] === \App\Models\Document::KIND_PAYMENT_RECEIPT) {
                    return number_format((float) $this->total);
                }
            })
        ];
    }

    public function cards(Request $request)
    {
        return [];
    }

    public function filters(Request $request)
    {
        return [];
    }
}
