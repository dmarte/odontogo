<?php

use App\Models\Team;
use App\Models\User;
use App\Pivots\TeamUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'author_user_id');
            $table->foreignIdFor(Team::class)
                ->cascadeOnUpdate()
                ->cascadeOnDeletete();
            $table->foreignIdFor(User::class)
                ->cascadeOnUpdate()
                ->cascadeOnDeletete();
            $table->json('scopes');
            $table->string('status', 40)->default(TeamUser::STATUS_INVITED);
            $table->string('token')->unique();
            $table->dateTime('invited_at');
            $table->dateTime('joined_at')->nullable();
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
        Schema::dropIfExists('team_user');
    }
}
