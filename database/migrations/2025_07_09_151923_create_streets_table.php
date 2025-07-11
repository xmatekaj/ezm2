<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streets', function (Blueprint $table) {
            $table->id();
            $table->string('woj', 2); // Voivodeship code
            $table->string('pow', 2); // District code
            $table->string('gmi', 2); // Commune code
            $table->string('rodz_gmi', 1); // Commune type
            $table->string('sym', 7); // Locality symbol
            $table->string('sym_ul', 5); // Street symbol
            $table->string('cecha')->nullable(); // Street type (ul., al., etc.)
            $table->string('nazwa_1'); // Street name part 1
            $table->string('nazwa_2')->nullable(); // Street name part 2
            $table->date('stan_na'); // Date of data
            $table->timestamps();

            $table->index(['woj', 'pow', 'gmi', 'sym']);
            $table->index(['nazwa_1', 'nazwa_2']);
            $table->index(['woj', 'nazwa_1']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streets');
    }
};
