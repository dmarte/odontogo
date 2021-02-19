<?php

use App\Models\Budget;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Source;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->dateTime('at');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('status');
            $table->foreignIdFor(Budget::class)->nullable()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Doctor::class)->nullable()->updateOnCascade()->nullOnDelete();
            $table->foreignIdFor(Patient::class)->updateOnCascade()->cascadeOnDelete();
            $table->foreignIdFor(Team::class)->updateOnCascade()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'author_user_id')->updateOnCascade()->nullOnDelete();
            $table->foreignIdFor(Source::class)->nullable()->nullOnDelete()->cascadeOnUpdate();
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
        Schema::dropIfExists('appointments');
    }
}
