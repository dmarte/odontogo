<?php

namespace App\Observers;

use App\Models\Document;

class DocumentObserver
{
    public function creating(Document $document)
    {

        if (!$document->currency) {
            $document->exchange_rate = 1;
            $document->currency = $document->exchange_currency;
        }

        $document->summarize();
    }

    public function updating(Document $document)
    {
        $document->summarize();
    }
}
