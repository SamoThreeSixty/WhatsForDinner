<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id');
            $table->foreignId('category_id')->nullable()->after('category')->constrained('categories')->nullOnDelete();
            $table->index('category_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id');
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->unique('slug', 'ingredients_slug_unique');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug', 'products_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropUnique('ingredients_slug_unique');
            $table->dropIndex(['category_id']);
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn('slug');
        });

        Schema::dropIfExists('categories');
    }
};
