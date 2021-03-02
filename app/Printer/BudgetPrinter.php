<?php


namespace App\Printer;

use App\Models\Document;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\Country;

class BudgetPrinter extends Printer
{
    public function __construct(public Document $model)
    {
        parent::__construct('Letter', '', request()->user()->locale);

        $this->changeLanguageTerm('from', __('Team'));
        $this->changeLanguageTerm('to', __('Patient'));
        $this->changeLanguageTerm('number', __('Code'));
        $this->changeLanguageTerm('date', __('Date'));
        $this->changeLanguageTerm('due', __('Expiration'));
        $this->changeLanguageTerm('product', __('Service or procedure'));
        $this->changeLanguageTerm('vat', __('Tax'));
        $this->changeLanguageTerm('total', __('Total'));
        $this->changeLanguageTerm('page_of', __('of'));
        $this->changeLanguageTerm('page', __('Page'));
        $this->columnOpacity = 0;

        if (Storage::disk($model->team->avatar_disk)->exists($model->team->avatar_path)) {
            $this->setLogo(Storage::disk($model->team->avatar_disk)->path($model->team->avatar_path));
        }

//        $this->setColor('#5e5e5e');
        $this->setFontSizeProductDescription(9);
        $this->setType($model->sequence->title);
        $this->setReference($model->sequence_value);
        $this->setDate($model->emitted_at->format('d/m/Y'));
        $this->setDue($model->expire_at->format('d/m/Y'));
        $this->flipflop();

        $this->addParagraph(__('The currency used in this document is :currency', ['currency'=>$model->currency]));

        if ($this->model->description) {
            $this->addParagraph($model->description);
        }

        $this->setFrom(array_filter([
            $model->team->name,
            $model->team->vat ? __(strtolower($model->team->country)."_tax_payer_number").': '.$model->team->vat : null,
            $model->team->address_line_1 ?? null,
            collect((new Country(''))->meta['options'])->firstWhere('value', $model->team->country)['label'] ?? $model->team->country,
            $model->team->phone_primary,
        ]));
        $this->setTo(array_filter([
            $model->receiver->tax_payer_name,
            $model->receiver->tax_payer_number,
            $model->receiver->phone_primary,
            $model->receiver->address_line_1,
            $model->receiver->address_line_2,
        ]));
        $model->items->each(function (Item $item) {
            $this->addItem(
                "{$item->product->code} - {$item->product->name}",
                $item->description,
                $item->quantity,
                false,
                $item->price,
                $item->discounts,
                $item->total
            );
        });

        $this->addTotal(__('Discounts'), $model->discounts);
        $this->addTotal(__('Subtotal'), $model->subtotal);
        $this->addTotal(__('Taxes'), $model->taxes);
        $this->addTotal(__('Total'), $model->total, true);

        $this->addCustomHeader(__('Created by'), $model->author->name);
        $this->addCustomHeader(__('Printed at'), now()->setTimezone(auth()->user()->time_zone)->format('d/m/Y h:i A'));

        if ($this->model->provider) {
            $this->addCustomHeader(__('Doctor'), $this->model?->provider?->name);
        }

        $this->setFooternote($model->code);
    }
}
