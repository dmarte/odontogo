<?php


namespace App\Printer;

use App\Models\Document;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\Country;

class ReceiptPrinter extends Printer
{
    public function __construct(public Document $model)
    {
        parent::__construct('Letter', '', request()->user()->locale);

        $this->changeLanguageTerm('from', __('Team'));
        $this->changeLanguageTerm('to', __('Patient'));
        $this->changeLanguageTerm('number', __('Code'));
        $this->changeLanguageTerm('date', __('Date'));
//        $this->changeLanguageTerm('due', __('Expiration'));
        $this->changeLanguageTerm('discount', __('Amount paid'));
        $this->changeLanguageTerm('price', __('Amount to pay'));
        $this->changeLanguageTerm('product', __('Concept'));
        $this->changeLanguageTerm('vat', __('Tax'));
        $this->changeLanguageTerm('total', __('Pending balance'));
        $this->changeLanguageTerm('page_of', __('of'));
        $this->changeLanguageTerm('page', __('Page'));
        $this->columnOpacity = 0;

        $this->setLogo(Storage::disk($model->team->avatar_disk)->path($model->team->avatar_path));
        $this->setFontSizeProductDescription(9);
        $this->setType($model->sequence->title);
        $this->setReference($model->sequence_value);
        $this->setDate($model->emitted_at->format('d/m/Y'));
//        $this->setDue($model->expire_at->format('d/m/Y'));
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
                item: $item->product ? "{$item->product->code} - {$item->product->name}" : $item->title,
                description: join("\n" , array_filter([
                __('Payment method'). ': '. __(ucfirst(str_replace('_',' ', $item->data['method']))),
                !empty($item->data['confirmation_number']) ? __('Confirmation') . ': ' . $item->data['confirmation_number'] : null,
                !empty($item->data['credit_card_last_digits']) ? __('Card') . ': ' . $item->data['credit_card_last_digits'] : null,
                $item->product ? __('Price') . ': ' . number_format($item->product->price) : null,
                $item->product ? __('Quantity') . ': ' . number_format((float)$item->quantity) : null,
                $item->discount > 0 ? __('Discounts') . ': ' . number_format($item->discounts) : null,
            ])),
                quantity: false,
                vat: false,
                price: $item->total,
                discount: false,
                total: $item->balance,
            );

        });

        if($this->model->paid) {
            $this->addBadge(
                badge: __('Paid'),
                color: $this->model->team->primary_color
            );
        }

//        $this->addTotal(__('Amount to pay'), $model->total);
        $this->addTotal(__('Amount paid'), $model->total);
        $this->addTotal(__('Pending'), $model->balance, true);

        $this->addCustomHeader(__('Created by'), $model->author->name);
        $this->addCustomHeader(__('Printed at'), now()->setTimezone(auth()->user()->time_zone)->format('d/m/Y h:i A'));

        if ($this->model->receiver_contact_id !== $this->model->paid_by_contact_id) {
            $this->addCustomHeader(__('Payer'), $this->model->payer->name);
        }

        $this->setFooternote($model->code);
    }
}
