<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('water_meters', function (Blueprint $table) {
            $table->id();
            $table->date('installation_date')->nullable();
            $table->date('transmitter_installation_date')->nullable();
            $table->date('meter_expiry_date')->nullable();
            $table->date('transmitter_expiry_date')->nullable();
            $table->integer('meter_number');
            $table->integer('transmitter_number');
            $table->boolean('is_active')->default(true);
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('water_meters');
    }
};
