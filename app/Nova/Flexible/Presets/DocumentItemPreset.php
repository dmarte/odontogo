<?php

namespace App\Nova\Flexible\Presets;

use App\Models\Document;
use App\Nova\Flexible\Layouts\FlexibleItemLayout;
use App\Nova\Flexible\Resolvers\BudgetItemResolver;
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
            $field->addLayout(FlexibleItemLayout::class);
            $field->button(__('Add'));
            $field->collapsed(false);
            $field->fullWidth();
            $field->resolver(BudgetItemResolver::class);
    }

}
