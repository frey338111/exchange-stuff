<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('category', function (Blueprint $table) {
            $table->string('url_key')->nullable()->unique()->after('category_title');
        });

        Schema::table('product', function (Blueprint $table) {
            $table->string('url_key')->nullable()->unique()->after('product_name');
        });

        $usedUrlKeys = [];

        DB::table('category')
            ->orderBy('category_id')
            ->get(['category_id', 'category_title'])
            ->each(function (object $category) use (&$usedUrlKeys): void {
                $baseUrlKey = Str::slug($category->category_title);
                $baseUrlKey = $baseUrlKey !== '' ? $baseUrlKey : 'category-'.$category->category_id;
                $urlKey = $baseUrlKey;
                $suffix = 2;

                while (in_array($urlKey, $usedUrlKeys, true)) {
                    $urlKey = $baseUrlKey.'-'.$suffix;
                    $suffix++;
                }

                $usedUrlKeys[] = $urlKey;

                DB::table('category')
                    ->where('category_id', $category->category_id)
                    ->update(['url_key' => $urlKey]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropUnique('product_url_key_unique');
            $table->dropColumn('url_key');
        });

        Schema::table('category', function (Blueprint $table) {
            $table->dropUnique('category_url_key_unique');
            $table->dropColumn('url_key');
        });
    }
};
