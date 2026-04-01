<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->string('ingredient_text')->nullable();
            $table->decimal('amount', 10, 3)->nullable();
            $table->string('unit', 32)->nullable();
            $table->string('preparation_note', 255)->nullable();
            $table->boolean('is_optional')->default(false);
            $table->timestamps();

            $table->index(['recipe_id', 'position']);
            $table->index(['recipe_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
