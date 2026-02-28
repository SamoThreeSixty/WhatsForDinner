<?php

use App\Enums\UnitType;
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
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('household_id');
            $table->dropColumn('location');
            $table->dropColumn('unit_type');
            $table->dropColumn('unit');
            $table->dropColumn('quantity');
            $table->dropColumn('purchased_at');
            $table->dropColumn('expires_at');
            $table->dropColumn('batch_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['household_id', 'name']);
            $table->string('location')->nullable();
            $table->enum('unit_type', array_column(UnitType::cases(), 'value'))
                ->default(UnitType::Count->value);
            $table->string('unit', 32);
            $table->decimal('quantity', 12, 3);
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('batch_reference')->nullable();
        });
    }
};
