<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->date('change_date');
            $table->decimal('water_sewage_price', 10, 2);
            $table->decimal('garbage_price', 10, 2);
            $table->decimal('management_fee', 10, 2);
            $table->decimal('renovation_fund', 10, 2);
            $table->decimal('loan_fund', 10, 2);
            $table->decimal('central_heating_advance', 10, 2);
            $table->foreignId('community_id')->constrained('communities')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('prices');
    }
};
