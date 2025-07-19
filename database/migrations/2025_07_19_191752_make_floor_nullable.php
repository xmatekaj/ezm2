<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration to make floor column nullable
     */
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            // Make floor column nullable to allow partial data imports
            $table->integer('floor')->nullable()->change();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            // Note: This might fail if there are NULL values in the column
            $table->integer('floor')->nullable(false)->change();
        });
    }
};
