<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('apartment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('verification_type', ['company', 'owner']);

            // Verification data fields
            $table->decimal('last_settlement_amount', 10, 2)->nullable();
            $table->decimal('last_fee_amount', 10, 2)->nullable();
            $table->date('last_settlement_date')->nullable();
            $table->string('apartment_number')->nullable();
            $table->string('owner_name')->nullable();

            // Company verification fields
            $table->string('company_name')->nullable();
            $table->string('tax_id')->nullable(); // NIP
            $table->string('regon')->nullable();

            $table->json('verification_data')->nullable(); // Additional flexible data
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('verification_token')->unique();
            $table->timestamps();

            $table->index(['email', 'community_id']);
            $table->index(['verification_token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_verifications');
    }
};
