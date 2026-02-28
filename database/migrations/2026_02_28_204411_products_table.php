<?php

use App\Enums\UnitType;
use App\Models\Ingredient;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Ingredient::class, 'ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('company')->nullable();
            $table->string('name');

            // Unit dimension (mass/volume/count) plus allowed literal unit codes for this product.
            $table->enum('unit_type', array_column(UnitType::cases(), 'value'))->default(UnitType::Count->value);
            $table->json('unit_options')->nullable();
            $table->string('unit_default', 32);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ingredient_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
