<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For PostgreSQL, we need to update the check constraint
        if (config('database.default') === 'pgsql') {
            // First, drop the old constraint
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_two_factor_method_check");

            // Now we can safely update existing data
            DB::statement("UPDATE users SET two_factor_method = 'totp' WHERE two_factor_method = 'sms'");
            DB::statement("UPDATE users SET two_factor_method = 'totp' WHERE two_factor_method IS NULL");

            // Add new constraint with 'totp' and 'email' values
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_two_factor_method_check CHECK (two_factor_method::text = ANY (ARRAY['totp'::character varying, 'email'::character varying]::text[]))");
        } else {
            // For MySQL - update data first
            DB::statement("UPDATE users SET two_factor_method = 'totp' WHERE two_factor_method = 'sms'");
            DB::statement("UPDATE users SET two_factor_method = 'totp' WHERE two_factor_method IS NULL");

            Schema::table('users', function (Blueprint $table) {
                $table->enum('two_factor_method', ['totp', 'email'])->default('totp')->change();
            });
        }
    }

    public function down(): void
    {
        if (config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_two_factor_method_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_two_factor_method_check CHECK (two_factor_method::text = ANY (ARRAY['email'::character varying, 'sms'::character varying]::text[]))");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('two_factor_method', ['email', 'sms'])->default('email')->change();
            });
        }
    }
};
