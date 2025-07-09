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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('building_number', 10)->nullable();
            $table->string('apartment_number', 10);
            $table->decimal('area', 10, 2)->nullable();
            $table->decimal('basement_area', 10, 2)->nullable();
            $table->decimal('storage_area', 10, 2)->nullable();
            $table->decimal('heated_area', 10, 2)->nullable();
            $table->decimal('common_area_share', 5, 2)->nullable();
            $table->smallInteger('floor');
            $table->decimal('elevator_fee_coefficient', 3, 2)->default(1.00);
            $table->boolean('has_basement')->default(false);
            $table->boolean('has_storage')->default(false);
            $table->boolean('is_owned')->default(true);
            $table->boolean('is_commercial')->default(false);
            $table->foreignId('community_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
