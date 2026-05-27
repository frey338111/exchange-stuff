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
        Schema::create('claim_request', function (Blueprint $table) {
            $table->increments('request_id');
            $table->foreignId('customer_id')->constrained('customers', 'customer_id')->cascadeOnDelete();
            $table->unsignedInteger('listing_id');
            $table->foreignId('product_id')->constrained('product', 'product_id')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->dateTime('pickup_date')->nullable();
            $table->smallInteger('status')->default(0);

            $table->foreign('listing_id')->references('listing_id')->on('listing')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_request');
    }
};
