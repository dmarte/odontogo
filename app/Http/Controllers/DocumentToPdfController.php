<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Item;
use App\Printer\BudgetPrinter;
use App\Printer\DocumentPrinter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Konekt\PdfInvoice\InvoicePrinter;
use Laravel\Nova\Fields\Country;

class DocumentToPdfController extends Controller
{
    public function budget(Budget $budget)
    {
        $engine = new BudgetPrinter($budget);

        return $engine->viewOnline();
    }
}
