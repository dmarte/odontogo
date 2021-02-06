<?php

use App\Models\Contact;
use App\Models\Document;
use App\Models\Product;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->json('data')->nullable();

            $table->foreignIdFor(Document::class)->nullable();
            $table->foreignIdFor(Product::class)->nullable();

            $table->char('currency', 3);
            $table->integer('quantity', false, true)->default(0);
            $table->decimal('amount', 18, 2)->unsigned()->default(0);
            $table->decimal('amount_paid', 18, 2)->default(0);
            $table->decimal('price', 18, 2)->unsigned()->default(0);
            $table->decimal('taxes', 18, 2)->default(0);
            $table->decimal('discounts', 18, 2)->default(0);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->decimal('balance', 18, 2)->default(0);
            $table->decimal('change', 18, 2)->default(0);

            $table->boolean('paid')->default(false);
            $table->boolean('completed')->default(false);
            $table->boolean('cancelled')->default(false);
            $table->boolean('verified')->default(false);

            $table->dateTime('expire_at')->nullable();
            $table->dateTime('emitted_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('verified_at')->nullable();

            $table->foreignIdFor(Team::class, 'team_id')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(
                \App\Models\Attribute::class,
                'category_attribute_id'
            )->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(
                \App\Models\Attribute::class,
                'subcategory_attribute_id'
            )->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(Contact::class, 'provider_contact_id')->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(Contact::class, 'receiver_contact_id')->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(Contact::class, 'paid_by_contact_id')->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(User::class, 'author_user_id')->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(User::class, 'completed_by_user_id')->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(User::class, 'cancelled_by_user_id')->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by_user_id')->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(User::class, 'deleted_by_user_id')->nullable()->cascadeOnUpdate()->nullOnDelete();

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
        Schema::dropIfExists('documents_items');
    }
}
