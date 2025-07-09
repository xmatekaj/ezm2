<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'full_name',
        'address_street',
        'address_postal_code',
        'address_city',
        'address_state',
        'regon',
        'tax_id',
        'manager_name',
        'manager_address_street',
        'manager_address_postal_code',
        'manager_address_city',
        'common_area_size',
        'apartments_area',
        'short_full_name',
        'is_active',
        'has_elevator',
        'residential_water_meters',
        'main_water_meters',
        'apartment_count',
        'staircase_count',
        'color'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_elevator' => 'boolean',
        'common_area_size' => 'decimal:2',
        'apartments_area' => 'decimal:2',
    ];

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address_street}, {$this->address_postal_code} {$this->address_city}";
    }
}
