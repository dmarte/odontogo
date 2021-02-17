<?php

namespace App\Observers;

use App\Models\Attribute;
use App\Models\Catalog;
use App\Models\Doctor;

class DoctorObserver
{
    public function creating(Doctor $doctor) {
        $doctor->category_attribute_id = Catalog::expenses()->first()->id;
        $doctor->subcategory_attribute_id = Catalog::expenseBySalesCommission()->first()->id;
    }

    /**
     * Handle the Doctor "created" event.
     *
     * @param  \App\Models\Doctor  $doctor
     * @return void
     */
    public function created(Doctor $doctor)
    {
        //
    }

    /**
     * Handle the Doctor "updated" event.
     *
     * @param  \App\Models\Doctor  $doctor
     * @return void
     */
    public function updated(Doctor $doctor)
    {
        //
    }

    /**
     * Handle the Doctor "deleted" event.
     *
     * @param  \App\Models\Doctor  $doctor
     * @return void
     */
    public function deleted(Doctor $doctor)
    {
        //
    }

    /**
     * Handle the Doctor "restored" event.
     *
     * @param  \App\Models\Doctor  $doctor
     * @return void
     */
    public function restored(Doctor $doctor)
    {
        //
    }

    /**
     * Handle the Doctor "force deleted" event.
     *
     * @param  \App\Models\Doctor  $doctor
     * @return void
     */
    public function forceDeleted(Doctor $doctor)
    {
        //
    }
}
