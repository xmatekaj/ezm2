<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('two_factor_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 6);
            $table->enum('type', ['sms', 'email'])->default('email');
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'code']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('two_factor_codes');
    }
};
