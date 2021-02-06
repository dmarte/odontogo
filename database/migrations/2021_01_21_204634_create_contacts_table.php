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
            $table->string('avatar_path')->nullable();
            $table->string('avatar_disk')->default('public');
            $table->bigInteger('avatar_size')->default(0);
            $table->string('code')->index();
            $table->bigInteger('counter')->default(0);
            $table->string('kind')->index();
            $table->string('name');
            $table->string('tax_payer_name')->nullable();
            $table->string('tax_payer_number')->nullable();
            $table->string('identification_number')->nullable();
            $table->string('insurance_number')->nullable();
            $table->foreignIdFor(User::class)->nullable();
            $table->foreignIdFor(\App\Models\Attribute::class, 'insurance_attribute_id')->nullable();
            $table->foreignIdFor(\App\Models\Attribute::class, 'source_attribute_id')->nullable();
            $table->foreignIdFor(\App\Models\Attribute::class, 'category_attribute_id')->nullable();
            $table->foreignIdFor(\App\Models\Attribute::class, 'subcategory_attribute_id')->nullable();
            $table->foreignIdFor(\App\Models\Attribute::class, 'career_attribute_id')->nullable();
            $table->string('title')->nullable();
            $table->string('company')->nullable();
            $table->string('phone_primary')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('email_primary')->nullable();
            $table->string('email_secondary')->nullable();

            $table->text('notes')->nullable();
            $table->enum('gender', ['none', 'male', 'female'])->default('none');
            $table->char('country_code', 2)->default('DO');
            $table->char('currency_code', 3)->default('DOP');
            $table->date('birthday')->nullable();
            $table->date('registered_at');
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city_name')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 18, 12)->nullable();
            $table->decimal('longitude', 18, 12)->nullable();
            $table->foreignIdFor(\App\Models\Team::class);
            $table->foreignIdFor(User::class, 'author_user_id')->nullable();
            $table->foreignIdFor(User::class, 'updated_by_user_id')->nullable();
            $table->decimal('credit_value', 50, 2);
            $table->integer('credit_days',false, true);
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
