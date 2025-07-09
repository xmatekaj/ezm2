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
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('full_name');
            $table->string('address_street');
            $table->string('address_postal_code', 10);
            $table->string('address_city', 50);
            $table->string('address_state', 50)->nullable();
            $table->string('regon', 20)->unique();
            $table->string('tax_id', 20);
            $table->string('manager_name');
            $table->string('manager_address_street');
            $table->string('manager_address_postal_code', 10);
            $table->string('manager_address_city', 50);
            $table->decimal('common_area_size', 10, 2);
            $table->decimal('apartments_area', 10, 2);
            $table->string('short_full_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('has_elevator')->default(false);
            $table->integer('residential_water_meters')->default(0);
            $table->integer('main_water_meters')->default(0);
            $table->integer('apartment_count')->default(0);
            $table->integer('staircase_count')->default(0);
            $table->string('color', 7)->default('#000000');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
