<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->after('apartment_number');
            $table->string('intercom_code', 50)->nullable()->after('code');
            $table->dropColumn('heated_area');

            // Add unique constraint for code within community
            $table->unique(['community_id', 'code'], 'apartments_community_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropUnique('apartments_community_code_unique');
            $table->dropColumn(['code', 'intercom_code']);
            $table->decimal('heated_area', 10, 2)->nullable()->after('storage_area');
        });
    }
};
