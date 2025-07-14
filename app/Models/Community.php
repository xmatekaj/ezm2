<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'full_name',
        'internal_code', // Changed from short_full_name
        'address_street',
        'address_postal_code',
        'address_city',
        'address_state',
        'regon',
        'tax_id',
        // Removed manager fields - now in settings
        'common_area_size',
        'apartments_area',
        'apartment_count',
        'staircase_count',
        'has_elevator',
        'residential_water_meters',
        'main_water_meters',
        'is_active',
        'color',
    ];

    protected $casts = [
        'has_elevator' => 'boolean',
        'residential_water_meters' => 'integer',
        'main_water_meters' => 'integer',
        'is_active' => 'boolean',
        'common_area_size' => 'decimal:2',
        'apartments_area' => 'decimal:2',
    ];

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class);
    }

    public function waterMeters(): HasMany
    {
        return $this->hasMany(WaterMeter::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function getFullAddressAttribute(): string
    {
        return trim($this->address_street . ', ' . $this->address_postal_code . ' ' . $this->address_city);
    }

    /**
     * Get manager data from settings
     */
    public function getManagerDataAttribute(): array
    {
        return Setting::getManagerData();
    }

    /**
     * Get manager full address from settings
     */
    public function getManagerFullAddressAttribute(): string
    {
        $manager = Setting::getManagerData();
        return trim($manager['address_street'] . ', ' . $manager['address_postal_code'] . ' ' . $manager['address_city']);
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->color) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=' . ltrim($this->color, '#') . '&color=fff';
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }

    public function getAvatarPath(): string
    {
        $avatarPath = 'avatars/communities/' . $this->id . '.png';
        return Storage::disk('public')->exists($avatarPath) ? $avatarPath : null;
    }

    /**
     * Get address suggestions based on existing data
     */
    public static function getAddressSuggestions(string $field): array
    {
        return static::distinct()
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->pluck($field)
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get voivodeship options
     */
    public static function getVoivodeshipOptions(): array
    {
        return [
            'dolnośląskie' => 'Dolnośląskie',
            'kujawsko-pomorskie' => 'Kujawsko-pomorskie',
            'lubelskie' => 'Lubelskie',
            'lubuskie' => 'Lubuskie',
            'łódzkie' => 'Łódzkie',
            'małopolskie' => 'Małopolskie',
            'mazowieckie' => 'Mazowieckie',
            'opolskie' => 'Opolskie',
            'podkarpackie' => 'Podkarpackie',
            'podlaskie' => 'Podlaskie',
            'pomorskie' => 'Pomorskie',
            'śląskie' => 'Śląskie',
            'świętokrzyskie' => 'Świętokrzyskie',
            'warmińsko-mazurskie' => 'Warmińsko-mazurskie',
            'wielkopolskie' => 'Wielkopolskie',
            'zachodniopomorskie' => 'Zachodniopomorskie',
        ];
    }
}
