<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('counter')->unsigned();
            $table->char('prefix',4);
            $table->string('code')->index();
            $table->string('name');
            $table->decimal('price',40,2)->unsigned()->default(0);
            $table->char('currency', 3)->default('DOP');
            $table->foreignIdFor(\App\Models\Team::class);
            $table
                ->foreignId('insurance_attribute_id')
                ->nullable()
                ->references('id')
                ->on('attributes')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table
                ->foreignId('career_attribute_id')
                ->nullable()
                ->references('id')
                ->on('attributes')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table
                ->foreignId('author_user_id')
                ->nullable()
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
