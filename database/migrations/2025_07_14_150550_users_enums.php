<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, check if we're on PostgreSQL or MySQL
        $driver = config('database.default');

        if ($driver === 'pgsql') {
            // PostgreSQL: Drop and recreate the constraint
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type::text = ANY (ARRAY['super_admin'::character varying, 'owner'::character varying]::text[]))");
            DB::statement("ALTER TABLE users ALTER COLUMN user_type SET DEFAULT 'owner'");
        } else {
            // MySQL: Change the enum
            DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('super_admin', 'owner') DEFAULT 'owner'");
        }

        // Update any existing 'company' users to 'owner' if they still exist
        DB::table('users')
            ->where('user_type', 'company')
            ->update(['user_type' => 'owner']);
    }

    public function down(): void
    {
        $driver = config('database.default');

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type::text = ANY (ARRAY['super_admin'::character varying, 'company'::character varying, 'owner'::character varying]::text[]))");
        } else {
            DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('super_admin', 'company', 'owner') DEFAULT 'owner'");
        }
    }
};
