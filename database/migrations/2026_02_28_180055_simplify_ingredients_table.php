<?php

use App\Enums\UnitType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        DB::table('ingredients')->update(['name' => DB::raw('LOWER(name)')]);

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE ingredients ADD CONSTRAINT ingredients_name_lowercase_chk CHECK (name = LOWER(name))');
        }

        Schema::table('ingredients', function (Blueprint $table) {
            $table->unique('name', 'ingredients_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropUnique('ingredients_name_unique');
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            try {
                DB::statement('ALTER TABLE ingredients DROP CONSTRAINT ingredients_name_lowercase_chk');
            } catch (\Throwable) {
                DB::statement('ALTER TABLE ingredients DROP CHECK ingredients_name_lowercase_chk');
            }
        }

        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable()->after('id')->constrained()->nullOnDelete();
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
