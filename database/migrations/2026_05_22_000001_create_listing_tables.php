<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('listing', function (Blueprint $table) {
            $table->increments('listing_id');
            $table->foreignId('customer_id')->constrained('customers', 'customer_id')->cascadeOnDelete();
            $table->smallInteger('status')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('listing_items', function (Blueprint $table) {
            $table->unsignedInteger('listing_id');
            $table->foreignId('product_id')->constrained('product', 'product_id')->cascadeOnDelete();

            $table->foreign('listing_id')->references('listing_id')->on('listing')->cascadeOnDelete();
            $table->unique(['listing_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_items');
        Schema::dropIfExists('listing');
    }
};
