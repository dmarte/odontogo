<?php

namespace App\Nova;

use App\Models\Document;
use App\Nova\Metrics\DoctorTotalExpenses;
use Exception;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class ExpenseTransaction extends Resource
{
    public static $model = \App\Models\ExpenseTransaction::class;
    public static $displayInNavigation = false;
    public static $title = 'title';
    public static $search = [];
    public static $searchable = false;
    public static $globallySearchable = false;

    public static function label()
    {
        return __('Expenses distributions');
    }

    public static function singularLabel()
    {
        return __('Expense distribution');
    }

    public function fields(Request $request)
    {
        return [
            Date::make(__('Emitted at'), 'emitted_at')->hideWhenCreating()->hideWhenUpdating()->format('D MMM YYYY'),
            Hidden::make('document_id')->default($request->get('viaResourceId')),
            Text::make(__('Concept'),'title'),
            BelongsTo::make(__('Expense'), 'document', Expense::class)->onlyOnDetail(),
            BelongsTo::make(__('Doctor'), 'provider', Doctor::class)
                ->rules([
                    'required',
                    'numeric',
                ])
                ->withoutTrashed()
                ->searchable()
                ->showCreateRelationButton()
                ->withSubtitles()
                ->help(__('Set the doctor if this is an expense that should be charged to the doctor.')),
            BelongsTo::make(__('Patient'), 'receiver', Patient::class)
                ->rules([
                    'required',
                    'numeric',
                ])
                ->withoutTrashed()
                ->searchable()
                ->showCreateRelationButton()
                ->withSubtitles()
                ->help(__('Set a patient only if this is an expense related to that patient.')),
            Hidden::make('quantity')->default(1)->onlyOnForms(),
            Number::make(__('Expense'), 'price')
                ->rules([
                    'bail',
                    'required',
                    'numeric',
                    'min:1',
                    function ($attribute, $value, $fail) use ($request) {

                        try {
                            /** @var \App\Models\Expense $expense */
                            $expense = \App\Models\Expense::findOrFail($request->get('viaResourceId'));

                        } catch (Exception $exception) {

                            $fail(__('validation.exists', ['attribute' => __('Document')]));

                            return;
                        }

                        if ($request->route()->hasParameter('resourceId')) {

                            $sum = (float) $expense
                                ->items()
                                ->whereNotIn('id', [$request->route('resourceId')])
                                ->sum('total');
                        } else {
                            $sum = (float) $expense
                                ->items()
                                ->sum('total');
                        }

                        if ($sum >= $expense->total || ($sum + $value) > $expense->total) {
                            $fail(__('You cannot add this distribution because will exceed the expense total.'));
                        }

                    },
                ])
                ->displayUsing(fn($value) => number_format($value)),
        ];
    }


    public static function authorizedToCreate(Request $request)
    {
        $resource = explode('/', $request->route('view'))[1] ?? $request->get('viaResource');

        return ($resource === Expense::uriKey());
    }

}
