<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('two_factor_enabled')->default(true)->after('is_active');
            $table->enum('two_factor_method', ['email', 'sms'])->default('email')->after('two_factor_enabled');
            $table->timestamp('two_factor_verified_at')->nullable()->after('two_factor_method');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_enabled', 'two_factor_method', 'two_factor_verified_at']);
        });
    }
};
