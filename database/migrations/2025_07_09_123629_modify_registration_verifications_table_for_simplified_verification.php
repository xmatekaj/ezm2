<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_verifications', function (Blueprint $table) {
            // Remove ALL company-related and old verification columns
            $table->dropColumn([
                'verification_type',
                'last_settlement_amount',
                'last_settlement_date',
                'owner_name',
                'company_name',
                'tax_id',
                'regon',
                'verification_data'
            ]);

            // Add new simplified verification columns (same for everyone)
            $table->decimal('last_water_settlement_amount', 10, 2)->after('apartment_id');
            $table->decimal('last_water_prediction_amount', 10, 2)->after('last_water_settlement_amount');
            $table->integer('current_occupants')->after('last_water_prediction_amount');

            // Make apartment_number required
            $table->string('apartment_number')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('registration_verifications', function (Blueprint $table) {
            // Add back old columns
            $table->enum('verification_type', ['company', 'owner'])->after('apartment_id');
            $table->decimal('last_settlement_amount', 10, 2)->nullable()->after('verification_type');
            $table->date('last_settlement_date')->nullable()->after('last_settlement_amount');
            $table->string('owner_name')->nullable()->after('apartment_number');
            $table->string('company_name')->nullable()->after('owner_name');
            $table->string('tax_id')->nullable()->after('company_name');
            $table->string('regon')->nullable()->after('tax_id');
            $table->json('verification_data')->nullable()->after('regon');

            // Remove new columns
            $table->dropColumn([
                'last_water_settlement_amount',
                'last_water_prediction_amount',
                'current_occupants'
            ]);
        });
    }
};
