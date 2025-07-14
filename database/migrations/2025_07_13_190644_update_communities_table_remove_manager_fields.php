<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            // Change the field name from short_full_name to internal_code
            $table->renameColumn('short_full_name', 'internal_code');

            // Remove manager fields since they'll be moved to settings
            $table->dropColumn([
                'manager_name',
                'manager_address_street',
                'manager_address_postal_code',
                'manager_address_city'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            // Restore the original field name
            $table->renameColumn('internal_code', 'short_full_name');

            // Add back manager fields
            $table->string('manager_name')->after('tax_id');
            $table->string('manager_address_street')->after('manager_name');
            $table->string('manager_address_postal_code')->after('manager_address_street');
            $table->string('manager_address_city')->after('manager_address_postal_code');
        });
    }
};
