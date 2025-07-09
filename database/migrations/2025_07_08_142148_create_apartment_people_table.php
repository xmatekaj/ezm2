<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('apartment_person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->decimal('ownership_share', 5, 2)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Ensure only one primary person per apartment
            $table->unique(['apartment_id', 'is_primary']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('apartment_person');
    }
};
