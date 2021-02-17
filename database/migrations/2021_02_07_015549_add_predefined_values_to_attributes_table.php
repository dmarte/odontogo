<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPredefinedValuesToAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->unsignedDecimal('amount_debit', 40,20)->default(0);
            $table->unsignedDecimal('amount_credit', 40,20)->default(0);
            $table->boolean('system_default')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('system_default');
            $table->dropColumn('amount_debit');
            $table->dropColumn('amount_credit');
        });
    }
}
