<?php

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
        Schema::table('communities', function (Blueprint $table) {
            // 1. Add new total_area field (this replaces the general area concept)
            $table->decimal('total_area', 10, 2)->nullable()->after('apartments_area');

            // 2. Make REGON nullable and remove unique constraint
            // First drop the unique constraint
            $table->dropUnique(['regon']);
            // Then make it nullable
            $table->string('regon', 20)->nullable()->change();

            // 3. Make NIP/tax_id nullable
            $table->string('tax_id', 20)->nullable()->change();

            // 4. Make common_area_size nullable (we'll remove it from UI but keep in DB for now)
            $table->decimal('common_area_size', 10, 2)->nullable()->change();

            // 5. Make apartments_area nullable (technical parameter)
            $table->decimal('apartments_area', 10, 2)->nullable()->change();

            // 6. Make other technical parameters nullable by changing defaults
            $table->integer('apartment_count')->nullable()->default(null)->change();
            $table->integer('staircase_count')->nullable()->default(null)->change();
            $table->integer('residential_water_meters')->nullable()->default(null)->change();
            $table->integer('main_water_meters')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            // Remove the new total_area field
            $table->dropColumn('total_area');

            // Revert REGON to required with unique constraint
            // Note: This might fail if there are null values or duplicates
            $table->string('regon', 20)->nullable(false)->change();
            $table->unique('regon');

            // Revert tax_id to required
            $table->string('tax_id', 20)->nullable(false)->change();

            // Revert common_area_size to required
            $table->decimal('common_area_size', 10, 2)->nullable(false)->change();

            // Revert apartments_area to required
            $table->decimal('apartments_area', 10, 2)->nullable(false)->change();

            // Revert technical parameters to have defaults
            $table->integer('apartment_count')->nullable(false)->default(0)->change();
            $table->integer('staircase_count')->nullable(false)->default(0)->change();
            $table->integer('residential_water_meters')->nullable(false)->default(0)->change();
            $table->integer('main_water_meters')->nullable(false)->default(0)->change();
        });
    }
};
