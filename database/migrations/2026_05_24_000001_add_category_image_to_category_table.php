<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category', function (Blueprint $table) {
            $table->string('category_image')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('category', function (Blueprint $table) {
            $table->dropColumn('category_image');
        });
    }
};
