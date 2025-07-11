<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('territorial_units', function (Blueprint $table) {
            $table->id();
            $table->string('woj', 2); // Voivodeship code
            $table->string('pow', 2)->nullable(); // District code
            $table->string('gmi', 2)->nullable(); // Commune code
            $table->string('rodz', 1)->nullable(); // Type code
            $table->string('nazwa'); // Name
            $table->string('nazwa_dod')->nullable(); // Additional name (type description)
            $table->date('stan_na'); // Date of data
            $table->timestamps();

            $table->index(['woj', 'pow', 'gmi', 'rodz']);
            $table->index(['woj', 'nazwa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('territorial_units');
    }
};
