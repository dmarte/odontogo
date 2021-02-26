<?php


namespace App\Printer;


use App\Models\Doctor;
use Illuminate\Support\Facades\Storage;
use stdClass;

class DoctorReportPrinter extends Printer
{
    public function __construct(protected Doctor $model)
    {
        parent::__construct(size: 'letter', currency: '', language: app()->getLocale());
        $this->setLogo(Storage::disk($model->team->avatar_disk)->path($model->team->avatar_path));

        $this->setType($this->model->name);

        $this->columnOpacity = 0;
        $this->hide_tofrom();
        $this->changeLanguageTerm('product', __('Patient'));
        $this->changeLanguageTerm('vat', __('Expense'));
        $this->changeLanguageTerm('price', __('Payment'));
        $this->changeLanguageTerm('qty', __('Agreement'));
        $this->changeLanguageTerm('discount', __('Balance'));
        $this->changeLanguageTerm('total', __('Doctor'));

        $model->report->each(function (stdClass $item) {
            $this->addItem(
                item: $item->concept,
                description: $item->description,
                quantity: $item->agreement,
                vat: $item->expense,
                price: $item->income,
                discount: $item->balance,
                total: $item->doctor
            );
        });

        $this->addTotal(name: __('Total Incomes'), value: $model->report->sum('income'));
        $this->addTotal(name: __('Total Expenses'), value: $model->report->sum('expense'));
        $this->addTotal(name: __('Total Doctor'), value: $model->report->last()?->doctor ?? 0);
    }
}
