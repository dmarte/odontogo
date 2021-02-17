<?php

use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSequencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->char('prefix')->nullable();
            $table->char('suffix')->nullable();
            $table->json('types');
            $table->json('tax_payer_types')->nullable();
            $table->smallInteger('length', false, true)->default(0);
            $table->unsignedBigInteger('counter')->default(0);
            $table->unsignedBigInteger('initial_counter')->default(0);
            $table->unsignedBigInteger('maximum')->default(0);
            $table->date('expire_at')->nullable();
            $table->boolean('is_default')->default(false);
            $table
                ->foreignId('parent_sequence_id')
                ->nullable()
                ->references('id')
                ->on('sequences')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table
                ->foreignId('author_user_id')
                ->nullable()
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreignIdFor(Team::class);
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
        Schema::dropIfExists('sequences');
    }
}
