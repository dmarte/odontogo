<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Catalog extends Attribute
{
    public const CATALOG_INCOME = 4;
    public const CATALOG_EXPENSES = 6;
    public const CATALOG_EXPENSES_BY_SALES_COMMISSION = 601;
    public const CATALOG_EXPENSES_GENERAL = 61;
    public const CATALOG_INCOME_BY_SALES = 401;

    public static function expenses(): Builder
    {
        return static::whereNull('team_id')->where('code', static::CATALOG_EXPENSES);
    }

    public static function income(): Builder {
        return static::whereNull('team_id')->where('code', static::CATALOG_INCOME);
    }

    public static function expenseBySalesCommission(): Builder
    {
        return static::whereNull('team_id')->where('code', static::CATALOG_EXPENSES_BY_SALES_COMMISSION);
    }

    public static function expensesGeneral(): Builder
    {
        return static::whereNull('team_id')->where('code', static::CATALOG_EXPENSES_GENERAL);
    }

    public static function incomeBySales(): Builder
    {
        return static::whereNull('team_id')->where('code', static::CATALOG_INCOME_BY_SALES);
    }

}
