<?php

use App\Models\Wallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWalletToDocumentItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents_items', function (Blueprint $table) {
            //
            $table->foreignIdFor(Wallet::class, 'wallet_attribute_id')
            ->nullable()
            ->nullOnDelete()
            ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents_items', function (Blueprint $table) {
            //
            $table->dropColumn('wallet_attribute_id');
        });
    }
}
