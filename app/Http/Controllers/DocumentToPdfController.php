<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Doctor;
use App\Models\Document;
use App\Models\Item;
use App\Models\Receipt;
use App\Printer\BudgetPrinter;
use App\Printer\DoctorReportPrinter;
use App\Printer\DocumentPrinter;
use App\Printer\ReceiptPrinter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Konekt\PdfInvoice\InvoicePrinter;
use Laravel\Nova\Fields\Country;

class DocumentToPdfController extends Controller
{
    public function doctor(Request $request, Doctor $doctor) {

        $balance = 0;

        $doctor->report->transform(function(Item $item) use (&$balance){
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
                'concept'=> $item->receiver->name,
                'description'=> join(' | ', array_filter([
                    $item->emitted_at->format('d/M/Y'),
                    $item->title ?? $item->source,
                ])),
                'agreement'=> $agreement,
                'expense'=> $isExpense ? $item->total : 0,
                'income'=>!$isExpense ? $item->total : 0,
                'balance'=> $balance,
                'doctor' => $total,
            ];
        });


        return (new DoctorReportPrinter($doctor))->viewOnline();
    }

    public function budget(Budget $budget)
    {
        $engine = new BudgetPrinter($budget);

        return $engine->viewOnline();
    }

    public function receipt(Receipt $receipt) {
        $engine = new ReceiptPrinter($receipt);
        return $engine->viewOnline();
    }
}
