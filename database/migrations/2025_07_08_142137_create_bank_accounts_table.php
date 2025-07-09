<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number', 30);
            $table->string('swift', 50)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_postal_code', 10)->nullable();
            $table->string('address_city', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('community_id')->constrained('communities')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bank_accounts');
    }
};
