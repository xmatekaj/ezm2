<?php
// database/migrations/2025_07_09_140000_cleanup_users_table_remove_company_role.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove company_role column if it exists
        if (Schema::hasColumn('users', 'company_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('company_role');
            });
        }

        // Update any existing 'company' users to 'owner'
        DB::table('users')
            ->where('user_type', 'company')
            ->update(['user_type' => 'owner']);

        // Handle enum changes for PostgreSQL
        if (config('database.default') === 'pgsql') {
            // Drop the old constraint and add a new one
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type::text = ANY (ARRAY['super_admin'::character varying, 'owner'::character varying]::text[]))");

            // Set default value
            DB::statement("ALTER TABLE users ALTER COLUMN user_type SET DEFAULT 'owner'");
        } else {
            // For MySQL/other databases
            Schema::table('users', function (Blueprint $table) {
                $table->enum('user_type', ['super_admin', 'owner'])->default('owner')->change();
            });
        }
    }

    public function down(): void
    {
        // Add back company_role column
        Schema::table('users', function (Blueprint $table) {
            $table->enum('company_role', ['admin', 'accountant', 'manager'])->nullable()->after('user_type');
        });

        // Handle enum changes for PostgreSQL
        if (config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type::text = ANY (ARRAY['super_admin'::character varying, 'company'::character varying, 'owner'::character varying]::text[]))");
            DB::statement("ALTER TABLE users ALTER COLUMN user_type SET DEFAULT 'owner'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('user_type', ['super_admin', 'company', 'owner'])->default('owner')->change();
            });
        }
    }
};
