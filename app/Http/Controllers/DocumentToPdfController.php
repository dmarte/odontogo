<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Doctor;
use App\Models\Document;
use App\Models\Item;
use App\Models\Receipt;
use App\Printer\BudgetPrinter;
use App\Printer\DoctorReportPrinter;
use App\Printer\ReceiptPrinter;
use Illuminate\Http\Request;

class DocumentToPdfController extends Controller
{
    public function doctor(Request $request, Doctor $doctor)
    {
        return (new DoctorReportPrinter($doctor, $request->get('from'), $request->get('to'),$request->user()->time_zone, $request->user()))->viewOnline();
    }

    public function budget(Budget $budget)
    {
        $engine = new BudgetPrinter($budget);

        return $engine->viewOnline();
    }

    public function receipt(Receipt $receipt)
    {
        $engine = new ReceiptPrinter($receipt);

        return $engine->viewOnline();
    }
}
