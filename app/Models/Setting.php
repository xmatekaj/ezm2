<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'label',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Setting categories
     */
    public static function getCategories(): array
    {
        return [
            'manager' => 'Zarządca',
            'application' => 'Aplikacja',
            'notifications' => 'Powiadomienia',
            'financial' => 'Finanse',
            'system' => 'System',
        ];
    }

    /**
     * Setting definitions with labels and categories
     */
    public static function getSettingDefinitions(): array
    {
        return [
            // Manager settings
            'manager_name' => [
                'label' => 'Nazwa zarządcy',
                'category' => 'manager',
                'type' => 'string',
            ],
            'manager_address_street' => [
                'label' => 'Ulica zarządcy',
                'category' => 'manager',
                'type' => 'string',
            ],
            'manager_address_postal_code' => [
                'label' => 'Kod pocztowy zarządcy',
                'category' => 'manager',
                'type' => 'string',
            ],
            'manager_address_city' => [
                'label' => 'Miasto zarządcy',
                'category' => 'manager',
                'type' => 'string',
            ],
            'manager_nip' => [
                'label' => 'NIP zarządcy',
                'category' => 'manager',
                'type' => 'string',
            ],
            'manager_regon' => [
                'label' => 'REGON zarządcy',
                'category' => 'manager',
                'type' => 'string',
            ],

            // Application settings
            'app_initialized' => [
                'label' => 'Aplikacja zainicjowana',
                'category' => 'application',
                'type' => 'boolean',
            ],
            'app_name' => [
                'label' => 'Nazwa aplikacji',
                'category' => 'application',
                'type' => 'string',
            ],
            'default_currency' => [
                'label' => 'Domyślna waluta',
                'category' => 'application',
                'type' => 'string',
            ],

            // Notification settings
            'email_notifications_enabled' => [
                'label' => 'Powiadomienia email',
                'category' => 'notifications',
                'type' => 'boolean',
            ],
            'sms_notifications_enabled' => [
                'label' => 'Powiadomienia SMS',
                'category' => 'notifications',
                'type' => 'boolean',
            ],

            // Financial settings
            'default_payment_deadline_days' => [
                'label' => 'Domyślny termin płatności (dni)',
                'category' => 'financial',
                'type' => 'integer',
            ],
            'late_payment_fee_percentage' => [
                'label' => 'Opłata za zwłokę (%)',
                'category' => 'financial',
                'type' => 'string',
            ],

            // System settings
            'backup_enabled' => [
                'label' => 'Kopie zapasowe włączone',
                'category' => 'system',
                'type' => 'boolean',
            ],
            'log_retention_days' => [
                'label' => 'Przechowywanie logów (dni)',
                'category' => 'system',
                'type' => 'integer',
            ],
        ];
    }

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember("setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'string'): void
    {
        $definitions = static::getSettingDefinitions();
        $definition = $definitions[$key] ?? [];

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => static::prepareValue($value, $type),
                'type' => $type,
                'category' => $definition['category'] ?? 'application',
                'label' => $definition['label'] ?? $key,
            ]
        );

        Cache::forget("setting_{$key}");
    }

    /**
     * Cast value based on type
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Prepare value for storage
     */
    protected static function prepareValue($value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Get manager data as array
     */
    public static function getManagerData(): array
    {
        return [
            'name' => static::get('manager_name', ''),
            'address_street' => static::get('manager_address_street', ''),
            'address_postal_code' => static::get('manager_address_postal_code', ''),
            'address_city' => static::get('manager_address_city', ''),
            'nip' => static::get('manager_nip', ''),
            'regon' => static::get('manager_regon', ''),
        ];
    }

    /**
     * Set manager data
     */
    public static function setManagerData(array $data): void
    {
        foreach ($data as $key => $value) {
            static::set("manager_{$key}", $value);
        }
    }

    /**
     * Check if manager is configured
     */
    public static function isManagerConfigured(): bool
    {
        $manager = static::getManagerData();
        return !empty($manager['name']) && !empty($manager['address_street']);
    }

    /**
     * Get settings by category
     */
    public static function getByCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('category', $category)->get();
    }

    /**
     * Get friendly label for setting key
     */
    public function getFriendlyLabelAttribute(): string
    {
        $definitions = static::getSettingDefinitions();
        return $definitions[$this->key]['label'] ?? $this->key;
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        $categories = static::getCategories();
        return $categories[$this->category] ?? $this->category;
    }
}