<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('apartment_occupancies', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('number_of_occupants');
            $table->date('change_date');
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('apartment_occupancies');
    }
};
