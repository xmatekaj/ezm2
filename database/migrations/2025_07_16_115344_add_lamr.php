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
        // Add land_mortgage_register to communities table
        Schema::table('communities', function (Blueprint $table) {
            $table->string('land_mortgage_register', 50)->nullable()->after('tax_id');
        });

        // Add land_mortgage_register to apartments table
        Schema::table('apartments', function (Blueprint $table) {
            $table->string('land_mortgage_register', 50)->nullable()->after('intercom_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn('land_mortgage_register');
        });

        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn('land_mortgage_register');
        });
    }
};
