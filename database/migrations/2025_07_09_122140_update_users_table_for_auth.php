<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->enum('user_type', ['super_admin', 'company', 'owner'])->default('owner')->after('email');
            $table->enum('company_role', ['admin', 'accountant', 'manager'])->nullable()->after('user_type');
            $table->string('phone')->nullable()->after('company_role');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->foreignId('person_id')->nullable()->constrained()->cascadeOnDelete()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->dropColumn([
                'first_name', 'last_name', 'user_type', 'company_role',
                'phone', 'is_active', 'last_login_at', 'person_id'
            ]);
        });
    }
};

