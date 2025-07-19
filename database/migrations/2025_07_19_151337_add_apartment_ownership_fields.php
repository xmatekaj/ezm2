<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            // City ownership fields
            $table->boolean('owned_by_city')->default(false)->after('community_id');
            $table->decimal('city_total_area', 10, 2)->nullable()->after('owned_by_city')
                ->comment('Total area of all city apartments when individual areas are unknown');
            $table->integer('city_apartment_count')->nullable()->after('city_total_area')
                ->comment('Number of city apartments this record represents');
            $table->decimal('city_common_area_share', 10, 2)->nullable()->after('city_apartment_count')
                ->comment('Common area share for all city apartments combined');

            // Index for performance
            $table->index(['community_id', 'owned_by_city']);
        });
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropIndex(['community_id', 'owned_by_city']);
            $table->dropColumn([
                'owned_by_city',
                'city_total_area',
                'city_apartment_count',
                'city_common_area_share'
            ]);
        });
    }
};
