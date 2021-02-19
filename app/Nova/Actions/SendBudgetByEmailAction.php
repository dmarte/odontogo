<?php

namespace App\Nova\Actions;

use App\Models\Contact;
use App\Notifications\BudgetSendNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class SendBudgetByEmailAction extends Action
{
    use InteractsWithQueue, Queueable;

    public function name()
    {
        return __('Send by email');
    }


    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        //

//        try {
            /** @var Contact $contact */
            $contact = Contact::findOrFail($fields->get('to'));
            $contact->notify(
                new BudgetSendNotification(
                    budgets: $models,
                    subject: $fields->get('subject'),
                    message: $fields->get('message'),
                    author: request()->user(),
                )
            );
            // Be sure to delete any generated pdf
            $models->each(fn($model) => $model->pdf->remove());
//        } catch (Exception $exception) {
//
//            Log::error($exception->getMessage());
//
//            return Action::danger(__('Something wrong while trying to send the budget.'));
//        }

        return Action::message(__('The budget has been sent.'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make(__('Contact'), 'to')
                ->searchable()
                ->options(function () {
                    return request()->user()->team->contacts()->whereNotNull('email_primary')->pluck('name', 'id');
                })
                ->rules(['required', 'numeric']),
            Text::make(__('Subject'), 'subject')
                ->default(__('Dental budget'))
                ->rules(['required']),
            Textarea::make(__('Message'), 'message')
                ->default(__('With this email you could find a document attached to it.')),
        ];
    }
}
