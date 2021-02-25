<?php

use App\Models\Source;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgreementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->numericMorphs('model');
            $table->decimal('unit_value', 18,2)->unsigned()->default(0);
            $table->enum('unit_type', ['percent','fix'])->default('percent');
            $table->boolean('used_after_expenses')->default(false);
            $table->foreignIdFor(Source::class, 'source_attribute_id')->nullable();
            $table->foreignIdFor(\App\Models\Attribute::class, 'insurance_attribute_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agreements');
    }
}
