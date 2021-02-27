<?php

use App\Models\Agreement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToAgreementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agreements', function (Blueprint $table) {
            //
            $table->string('kind')->index()->default(Agreement::KIND_SPLIT)->after('model_id');
            $table->enum('unit_action', [Agreement::ACTION_INCREASE, Agreement::ACTION_DECREASE])->default(Agreement::ACTION_DECREASE)->after('unit_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agreements', function (Blueprint $table) {
            //
            $table->dropColumn('kind');
            $table->dropColumn('unit_action');
        });
    }
}
