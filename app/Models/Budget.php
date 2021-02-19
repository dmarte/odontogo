<?php

namespace App\Models;

use App\Printer\BudgetPrinter;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Budget
 *
 * @package App\Models
 * @property-read BudgetPrinter $pdf
 */
class Budget extends Document
{
    public function getPdfAttribute() {
        return new BudgetPrinter($this);
    }
}
