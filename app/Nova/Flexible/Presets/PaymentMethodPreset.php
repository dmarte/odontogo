<?php

namespace App\Nova\Flexible\Presets;

use App\Nova\Flexible\Layouts\PaymentMethodLayout;
use App\Nova\Flexible\Resolvers\PaymentMethodResolver;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Preset;

class PaymentMethodPreset extends Preset
{
    /**
     * Execute the preset configuration
     *
     * @return void
     */
    public function handle(Flexible $field)
    {
        // You can call all available methods on the Flexible field.
         $field->addLayout(PaymentMethodLayout::class);
         $field->button(__('Add another payment distribution'));
         $field->fullWidth();
         $field->resolver(PaymentMethodResolver::class);
    }

}
