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
        Schema::create('product', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('product_name');
            $table->string('sku');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('category', 'category_id')->cascadeOnDelete();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('condition');
            $table->timestamps();

            $table->foreign('condition')->references('condition_id')->on('product_condition')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
