<?php

namespace App\Observers;

use App\Models\Contact;
use Illuminate\Support\Facades\Storage;

class ContactObserver
{
    public function creating(Contact $contact)
    {
        $contact->counter = $this->counter($contact);
        $contact->code = $this->code($contact, $contact->counter);

        if (is_null($contact->registered_at)) {
            $contact->registered_at = now()->format('Y-m-d');
        }

        if(is_null($contact->tax_payer_name)) {
            $contact->tax_payer_name = $contact->name;
        }

        if(is_null($contact->tax_payer_number)) {
            $contact->tax_payer_number = $contact->identification_number;
        }

        if (is_null($contact->tax_payer_type)) {
            $contact->tax_payer_type = config("ogo.{$contact->team->country}.contributors.default_type");
        }

        if (is_null($contact->sequence_id)) {
            $contact->sequence_id = $contact->team
                ->sequencesForInvoices()
                ->where('prefix', config("ogo.{$contact->team->country}.contributors.default_fiscal"))
                ->whereJsonContains('tax_payer_types', $contact->tax_payer_type)
                ->first()
                ?->id;
        }
    }

    public function updating(Contact $contact) {

        if (is_null($contact->avatar_path)) {
            $contact->avatar_size = 0;
        }
    }

    public function forceDeleted(Contact $contact) {

        if (!is_null($contact->avatar_path)) {
            Storage::disk($contact->avatar_disk)->delete($contact->avatar_path);
        }
    }

    private function counter(Contact $contact): int
    {
        return Contact::where('kind', $contact->kind)->where('team_id', $contact->team_id)->orderBy('counter', 'desc')->max('counter') + 1;
    }

    private function code(Contact $contact, int $counter): string
    {
        $prefix = $contact->kind;
        $length = 6;

        return $prefix . str_pad($counter, $length, '0', STR_PAD_LEFT);
    }
}
