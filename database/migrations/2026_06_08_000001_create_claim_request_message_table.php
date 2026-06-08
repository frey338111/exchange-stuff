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
        Schema::create('claim_request_message', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->foreignId('customer_id')->constrained('customers', 'customer_id')->cascadeOnDelete();
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('request_id')->references('request_id')->on('claim_request')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_request_message');
    }
};
