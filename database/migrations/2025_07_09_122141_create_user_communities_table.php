<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_communities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->enum('access_type', ['company', 'owner'])->default('owner');
            $table->json('permissions')->nullable(); // For future role-based permissions
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // For temporary access
            $table->timestamps();

            $table->unique(['user_id', 'community_id', 'access_type']);
            $table->index(['user_id', 'community_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_communities');
    }
};
