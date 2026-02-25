<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['household_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropIndex(['household_id', 'name']);
            $table->dropConstrainedForeignId('household_id');
        });
    }
};
