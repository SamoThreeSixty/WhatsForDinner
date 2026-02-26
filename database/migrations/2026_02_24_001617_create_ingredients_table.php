<?php

use App\Enums\UnitType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('location')->nullable();

            // Unit code/label (e.g. g, kg, ml, each, bag) with enum for conversion dimension.
            $table->enum('unit_type', array_column(UnitType::cases(), 'value'))
                ->default(UnitType::Count->value);
            $table->string('unit', 32);
            $table->decimal('quantity', 12, 3);

            // Supports expiration-aware stock and grouped purchases.
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('batch_reference')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
