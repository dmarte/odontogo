<?php

namespace App\Observers;

use App\Models\Catalog;
use App\Models\Patient;

class PatientObserver
{
    public function creating(Patient $patient) {
        $patient->category_attribute_id = Catalog::income()->first()->id;
        $patient->subcategory_attribute_id = Catalog::incomeBySales()->first()->id;
    }
}
