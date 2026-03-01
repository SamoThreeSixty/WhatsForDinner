<?php

use App\Models\Household;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Household::class, 'household_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Product::class, 'product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 32);
            $table->date('purchased_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('category')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['household_id', 'created_at']);
            $table->index(['household_id', 'category']);
            $table->index(['household_id', 'location']);
            $table->index(['household_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
