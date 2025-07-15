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
        'internal_code',
        'address_street',
        'address_postal_code',
        'address_city',
        'address_state',
        'regon',
        'tax_id',
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

    /**
     * Validation rules for form
     */
     public static function getValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'internal_code' => ['nullable', 'string', 'max:255'],
            'address_street' => ['required', 'string', 'max:255'],
            'address_postal_code' => ['required', 'string', 'max:10', 'regex:/^\d{2}-\d{3}$/'],
            'address_city' => ['required', 'string', 'max:50'],
            'address_state' => ['required', 'string', 'max:50'],
            'regon' => ['nullable', 'string', 'max:20', 'regex:/^\d{9}$|^\d{14}$/'],
            'tax_id' => ['nullable', 'string', 'max:20', 'regex:/^\d{10}$|^\d{3}-\d{3}-\d{2}-\d{2}$/'],
            'total_area' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'apartments_area' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'apartment_count' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'staircase_count' => ['nullable', 'integer', 'min:0', 'max:99'],
            'has_elevator' => ['boolean'],
            'residential_water_meters' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'main_water_meters' => ['nullable', 'integer', 'min:0', 'max:99'],
            'is_active' => ['boolean'],
            'color' => ['string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    /**
     * Get formatted REGON for display
     */
    public function getFormattedRegonAttribute(): ?string
    {
        if (!$this->regon) return null;

        if (strlen($this->regon) === 9) {
            return substr($this->regon, 0, 3) . '-' . substr($this->regon, 3, 3) . '-' . substr($this->regon, 6, 3);
        }

        if (strlen($this->regon) === 14) {
            return substr($this->regon, 0, 3) . '-' . substr($this->regon, 3, 3) . '-' . substr($this->regon, 6, 2) . '-' . substr($this->regon, 8, 6);
        }

        return $this->regon;
    }

    /**
     * Get formatted NIP for display
     */
    public function getFormattedTaxIdAttribute(): ?string
    {
        if (!$this->tax_id) return null;

        // Remove existing formatting
        $clean = preg_replace('/[^0-9]/', '', $this->tax_id);

        if (strlen($clean) === 10) {
            return substr($clean, 0, 3) . '-' . substr($clean, 3, 3) . '-' . substr($clean, 6, 2) . '-' . substr($clean, 8, 2);
        }

        return $this->tax_id;
    }

    /**
     * Get completion percentage for the community profile
     */
    public function getCompletionPercentageAttribute(): int
    {
        $fields = [
            'name', 'full_name', 'address_street', 'address_postal_code',
            'address_city', 'address_state', 'regon', 'tax_id', 'total_area',
            'apartments_area', 'apartment_count', 'staircase_count'
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }

    /**
     * Check if community has minimum required data
     */
    public function hasMinimumData(): bool
    {
        return !empty($this->name) &&
               !empty($this->full_name) &&
               !empty($this->address_street) &&
               !empty($this->address_city);
    }

    /**
     * Get area utilization percentage
     */
    public function getAreaUtilizationAttribute(): ?float
    {
        if (!$this->total_area || !$this->apartments_area) {
            return null;
        }

        return round(($this->apartments_area / $this->total_area) * 100, 2);
    }

    /**
     * Scope for active communities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for communities with elevators
     */
    public function scopeWithElevator($query)
    {
        return $query->where('has_elevator', true);
    }

    /**
     * Scope for communities by state
     */
    public function scopeInState($query, string $state)
    {
        return $query->where('address_state', $state);
    }

    /**
     * Get communities statistics
     */
    public static function getStatistics(): array
    {
        return [
            'total' => static::count(),
            'active' => static::active()->count(),
            'with_elevator' => static::withElevator()->count(),
            'total_apartments' => static::sum('apartment_count'),
            'total_area' => static::sum('total_area'),
            'avg_apartments_per_community' => static::avg('apartment_count'),
            'completion_rates' => [
                'high' => static::whereNotNull('regon')
                               ->whereNotNull('tax_id')
                               ->whereNotNull('total_area')
                               ->count(),
                'medium' => static::whereNotNull('regon')
                                 ->orWhereNotNull('tax_id')
                                 ->orWhereNotNull('total_area')
                                 ->count(),
            ]
        ];
    }
}
