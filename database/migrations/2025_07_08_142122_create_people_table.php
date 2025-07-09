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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 30);
            $table->string('last_name', 30);
            $table->string('email', 50)->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('correspondence_address_street')->nullable();
            $table->string('correspondence_address_postal_code', 10)->nullable();
            $table->string('correspondence_address_city', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->decimal('ownership_share', 5, 2)->nullable();
            $table->foreignId('spouse_id')->nullable()->constrained('people');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
