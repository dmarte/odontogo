<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('company')->nullable();
            $table->json('phones')->nullable();
            $table->json('emails')->nullable();
            $table->text('notes')->nullable();
            $table->enum('gender', ['none','male', 'female'])->default('none');
            $table->string('kind')->index();
            $table->char('country', 2)->default('DO');
            $table->char('currency', 3)->default('DOP');
            $table->date('birthday')->nullable();
            $table->date('checkin_at');
            $table->foreignIdFor(User::class, 'author_user_id')->nullable();
            $table->foreignIdFor(User::class, 'updated_by_user_id')->nullable();
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
        Schema::dropIfExists('contacts');
    }
}
