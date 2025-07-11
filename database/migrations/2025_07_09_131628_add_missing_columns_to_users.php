<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add columns only if they don't exist
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }

            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }

            if (!Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['super_admin', 'owner'])->default('owner')->after('email');
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('user_type');
            }

            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('phone');
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }

            if (!Schema::hasColumn('users', 'person_id')) {
                $table->foreignId('person_id')->nullable()->constrained()->cascadeOnDelete()->after('last_login_at');
            }

            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(true)->after('person_id');
            }

            if (!Schema::hasColumn('users', 'two_factor_method')) {
                $table->enum('two_factor_method', ['email', 'sms'])->default('email')->after('two_factor_enabled');
            }

            if (!Schema::hasColumn('users', 'two_factor_verified_at')) {
                $table->timestamp('two_factor_verified_at')->nullable()->after('two_factor_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->dropColumn([
                'first_name',
                'last_name',
                'user_type',
                'phone',
                'is_active',
                'last_login_at',
                'person_id',
                'two_factor_enabled',
                'two_factor_method',
                'two_factor_verified_at'
            ]);
        });
    }
};
