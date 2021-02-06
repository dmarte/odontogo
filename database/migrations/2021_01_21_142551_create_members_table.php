<?php

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\Member;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'author_user_id');
            $table->foreignIdFor(Team::class)
                ->cascadeOnUpdate()
                ->cascadeOnDeletete();
            $table->foreignIdFor(User::class)
                ->cascadeOnUpdate()
                ->cascadeOnDeletete();
            $table->foreignIdFor(Role::class)
                ->cascadeOnUpdate()
                ->cascadeOnDeletete();
            $table->foreignIdFor(\App\Models\Contact::class)->nullable();
            $table->string('status', 40)->default(Member::STATUS_INVITED);
            $table->string('token')->unique();
            $table->dateTime('invited_at');
            $table->dateTime('joined_at')->nullable();
            $table->boolean('is_team_owner');
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
        Schema::dropIfExists('members');
    }
}
