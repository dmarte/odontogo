<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignIdFor(User::class);
            $table->string('avatar_path')->nullable();
            $table->string('avatar_disk')->default('public');
            $table->bigInteger('avatar_size')->default(0);
            $table->string('phone_primary')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('email')->nullable();
            $table->char('country', 2)->default('DO');
            $table->char('currency', 3)->default('DOP');
            $table->char('locale',2)->default('es');
            $table->string('time_zone')->default('America/Santo_domingo');
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
        Schema::dropIfExists('teams');
    }
}
