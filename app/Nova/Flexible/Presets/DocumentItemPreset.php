<?php

namespace App\Nova\Flexible\Presets;

use App\Models\Document;
use App\Nova\Flexible\Layouts\DocumentItemLayout;
use App\Nova\Flexible\Resolvers\DocumentItemResolver;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Preset;

class DocumentItemPreset extends Preset
{
    public function __construct(private NovaRequest $request)
    {
    }

    public function handle(Flexible $field)
    {
            $field->addLayout(DocumentItemLayout::class);
            $field->button(__('Add procedure row'));
            $field->collapsed(false);
            $field->fullWidth();
            $field->resolver(DocumentItemResolver::class);
        // You can call all available methods on the Flexible field.
        // $field->addLayout(...)
        // $field->button(...)
        // $field->resolver(...)
        // ... and so on.
    }

}
