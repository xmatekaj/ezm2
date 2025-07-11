<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, update any existing 'company' access_type to 'owner'
        DB::table('user_communities')
            ->where('access_type', 'company')
            ->update(['access_type' => 'owner']);

        // For PostgreSQL, we need to handle enum changes differently
        if (config('database.default') === 'pgsql') {
            // Drop the old constraint and add a new one
            DB::statement("ALTER TABLE user_communities DROP CONSTRAINT IF EXISTS user_communities_access_type_check");
            DB::statement("ALTER TABLE user_communities ADD CONSTRAINT user_communities_access_type_check CHECK (access_type::text = 'owner'::text)");

            // Set default value
            DB::statement("ALTER TABLE user_communities ALTER COLUMN access_type SET DEFAULT 'owner'");
        } else {
            // For MySQL/other databases
            Schema::table('user_communities', function (Blueprint $table) {
                $table->enum('access_type', ['owner'])->default('owner')->change();
            });
        }
    }

    public function down(): void
    {
        // For PostgreSQL
        if (config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE user_communities DROP CONSTRAINT IF EXISTS user_communities_access_type_check");
            DB::statement("ALTER TABLE user_communities ADD CONSTRAINT user_communities_access_type_check CHECK (access_type::text = ANY (ARRAY['company'::character varying, 'owner'::character varying]::text[]))");
            DB::statement("ALTER TABLE user_communities ALTER COLUMN access_type SET DEFAULT 'owner'");
        } else {
            // For MySQL/other databases
            Schema::table('user_communities', function (Blueprint $table) {
                $table->enum('access_type', ['company', 'owner'])->default('owner')->change();
            });
        }
    }
};
