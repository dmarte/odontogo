<?php

namespace App\Observers;

use App\Models\Item;

class DocumentItemObserver
{

    public function created(Item $item) {
            $item->document->summarize()->save();
    }

    public function updating(Item $item) {
        $item->summarize();
    }

    public function updated(Item $item) {
        $item->document->summarize()->save();
    }
}
