<?php


namespace App\Printer;


use App\Models\Doctor;
use App\Models\Document;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use stdClass;

class DoctorReportPrinter extends Printer
{
    protected Collection $report;

    public function __construct(
        protected Doctor $model,
        private string|null $dateFrom,
        private string|null $dateTo,
        private string $time_zone = 'America/Santo_Domingo',
        private User $user
    )
    {
        parent::__construct(size: 'letter', currency: '', language: app()->getLocale());

        $this->buildReport();

        $this->setLogo(Storage::disk($model->team->avatar_disk)->path($model->team->avatar_path));

        $this->setType($this->model->name);
        $this->setDate(now($this->time_zone)->format('d/m/Y h:i A'));
        $this->addCustomHeader(__('Date from'), now($this->time_zone)->setTimestamp(strtotime($this->dateFrom))->format('d/m/Y'));
        $this->addCustomHeader(__('Date to'), now($this->time_zone)->setTimestamp(strtotime($this->dateTo))->format('d/m/Y'));
        $this->addCustomHeader(__('Printed by'), $this->user->name);

        $this->columnOpacity = 0;
        $this->hide_tofrom();
        $this->changeLanguageTerm('date', __('Emitted at'));
        $this->changeLanguageTerm('product', __('Patient'));
        $this->changeLanguageTerm('vat', __('Expense'));
        $this->changeLanguageTerm('price', __('Payment'));
        $this->changeLanguageTerm('qty', __('Agreement'));
        $this->changeLanguageTerm('discount', __('Balance'));
        $this->changeLanguageTerm('total', __('Doctor'));

        $this->buildReport();

        $this->report->each(function (stdClass $item) {
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

        $this->addTotal(name: __('Total Incomes'), value: $this->report->sum('income'));
        $this->addTotal(name: __('Total Expenses'), value: $this->report->sum('expense'));
        $this->addTotal(name: __('Total Doctor'), value: $this->report->last()?->doctor ?? 0);
    }

    public function buildReport()
    {
        $this->dateFrom = $this->dateFrom ?? now()->firstOfMonth()->format('Y-m-d');
        $this->dateTo = $this->dateTo ?? now()->format('Y-m-d');

        $balance = 0;

        $this->report=  $this
            ->model
            ->report()
            ->whereBetween('emitted_at', [$this->dateFrom, $this->dateTo])
            ->get()
            ->map(function (Item $item) use (&$balance) {
                $agreement = number_format($item->unit_value).'%';

                if ($item->unit_type === 'fix') {
                    $agreement = number_format($item->unit_value);
                }

                $isExpense = $item->data['kind'] === Document::KIND_EXPENSE;

                $balance = $balance + ($isExpense ? $item->total * -1 : $item->total);

                $total = $item->unit_value * $balance / 100;

                if ($item->unit_type === 'fix') {
                    $total = $balance < 0 ? $balance + ($item->unit_value * -1) : $balance + $item->unit_value;
                }

                return (object) [
                    'concept'     => $item->receiver->name,
                    'description' => join(' | ', array_filter([
                        $item->emitted_at->format('d/M/Y'),
                        $item->title ?? $item->source,
                    ])),
                    'agreement'   => $agreement,
                    'expense'     => $isExpense ? $item->total : 0,
                    'income'      => !$isExpense ? $item->total : 0,
                    'balance'     => $balance,
                    'doctor'      => $total,
                ];
            });
    }
}
