<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_verifications', function (Blueprint $table) {
            // Replace apartment_number with separate building and apartment
            $table->string('building_number')->nullable()->after('current_occupants');
            $table->string('apartment_number_new')->after('building_number');
            $table->string('phone')->nullable()->after('apartment_number_new');

            // Add territorial data
            $table->string('voivodeship')->nullable()->after('phone');
            $table->string('city')->nullable()->after('voivodeship');
            $table->string('street')->nullable()->after('city');
        });

        // Copy existing apartment_number to apartment_number_new
        DB::statement("UPDATE registration_verifications SET apartment_number_new = apartment_number");

        Schema::table('registration_verifications', function (Blueprint $table) {
            // Drop old apartment_number and rename new one
            $table->dropColumn('apartment_number');
        });

        Schema::table('registration_verifications', function (Blueprint $table) {
            $table->renameColumn('apartment_number_new', 'apartment_number');
        });
    }

    public function down(): void
    {
        Schema::table('registration_verifications', function (Blueprint $table) {
            $table->dropColumn([
                'building_number',
                'phone',
                'voivodeship',
                'city',
                'street'
            ]);
        });
    }
};
