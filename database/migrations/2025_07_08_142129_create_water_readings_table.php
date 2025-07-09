<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('water_readings', function (Blueprint $table) {
            $table->id();
            $table->decimal('reading', 10, 2);
            $table->timestamp('reading_date');
            $table->boolean('reverse_flow_alarm')->nullable();
            $table->boolean('magnet_alarm')->nullable();
            $table->foreignId('water_meter_id')->constrained('water_meters')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('water_readings');
    }
};
