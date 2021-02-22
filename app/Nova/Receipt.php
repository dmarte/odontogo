<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Titasgailius\SearchRelations\SearchesRelations;

class Receipt extends Resource
{
    use SearchesRelations;

    public static $tableStyle = 'tight';
    public static $perPageOptions = [10, 25, 50, 75, 100];
    public static $preventFormAbandonment = true;
    public static $model = \App\Models\Receipt::class;
    public static $search = [
        'code',
    ];

    public static $title = 'title';
    public static $globalSearchRelations = [
        'provider' => ['name', 'code'],
        'receiver' => ['name', 'code'],
    ];
    public static $searchRelations = [
        'provider' => ['name', 'code'],
        'receiver' => ['name', 'code'],
    ];

    public function subtitle()
    {
        return $this->code;
    }

    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
        ];
    }
}
