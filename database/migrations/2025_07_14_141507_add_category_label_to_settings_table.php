<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add new columns to existing settings table
        Schema::table('settings', function (Blueprint $table) {
            $table->string('category')->default('application')->after('type');
            $table->string('label')->nullable()->after('category');
            $table->index(['category']);
        });

        // Update existing settings with categories and labels
        $settingUpdates = [
            'manager_name' => ['category' => 'manager', 'label' => 'Nazwa zarządcy'],
            'manager_address_street' => ['category' => 'manager', 'label' => 'Ulica zarządcy'],
            'manager_address_postal_code' => ['category' => 'manager', 'label' => 'Kod pocztowy zarządcy'],
            'manager_address_city' => ['category' => 'manager', 'label' => 'Miasto zarządcy'],
            'app_initialized' => ['category' => 'application', 'label' => 'Aplikacja zainicjowana'],
        ];

        foreach ($settingUpdates as $key => $data) {
            DB::table('settings')
                ->where('key', $key)
                ->update($data);
        }

        // Add new manager settings if they don't exist
        $newSettings = [
            [
                'key' => 'manager_nip',
                'value' => '',
                'type' => 'string',
                'category' => 'manager',
                'label' => 'NIP zarządcy',
            ],
            [
                'key' => 'manager_regon',
                'value' => '',
                'type' => 'string',
                'category' => 'manager',
                'label' => 'REGON zarządcy',
            ],
        ];

        foreach ($newSettings as $setting) {
            $exists = DB::table('settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        // Remove new settings
        DB::table('settings')->whereIn('key', [
            'manager_nip',
            'manager_regon',
        ])->delete();

        // Remove columns
        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'label']);
        });
    }
};
