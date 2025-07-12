<?php
// database/migrations/2025_07_12_100000_enhance_apartment_types.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            // Add new apartment_type enum column
            $table->enum('apartment_type', ['residential', 'commercial', 'mixed', 'storage', 'garage'])
                  ->default('residential')
                  ->after('is_commercial');

            // Add more specific fields
            $table->text('usage_description')->nullable()->after('apartment_type');
            $table->boolean('has_separate_entrance')->default(false)->after('usage_description');
            $table->decimal('commercial_area', 10, 2)->nullable()->after('has_separate_entrance');
        });

        // Migrate existing data - PostgreSQL compatible
        if (config('database.default') === 'pgsql') {
            DB::statement("
                UPDATE apartments
                SET apartment_type = CASE
                    WHEN is_commercial = true THEN 'commercial'
                    ELSE 'residential'
                END
            ");
        } else {
            // MySQL/other databases
            DB::statement("
                UPDATE apartments
                SET apartment_type = CASE
                    WHEN is_commercial = 1 THEN 'commercial'
                    ELSE 'residential'
                END
            ");
        }
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn([
                'apartment_type',
                'usage_description',
                'has_separate_entrance',
                'commercial_area'
            ]);
        });
    }
};
