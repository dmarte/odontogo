<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BelongsTo;

class DocumentPrintAction extends Action
{
    use InteractsWithQueue, Queueable;

    public function name()
    {
        return __('Print');
    }


    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {

        if ($models->count() < 1) {
            return Action::danger(__('You should select a document to be printed.'));
        }

        /** @var \App\Models\Document $model */
        $model = $models->first();
        return Action::openInNewTab(url("/pdf/{$model->getMorphClass()}/{$model->getKey()}"));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
        ];
    }
}
