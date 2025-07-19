<?php

namespace App\Models;

use App\Enums\ApartmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_number',
        'apartment_number',
        'code',
        'intercom_code',
        'land_mortgage_register',
        'area',
        'basement_area',
        'storage_area',
        'common_area_share',
        'floor',
        'elevator_fee_coefficient',
        'has_basement',
        'has_storage',
        'is_commercial',
        'apartment_type',
        'usage_description',
        'has_separate_entrance',
        'commercial_area',
        'community_id',
        // City ownership fields
        'owned_by_city',
        'city_total_area',
        'city_apartment_count',
        'city_common_area_share'
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'basement_area' => 'decimal:2',
        'storage_area' => 'decimal:2',
        'commercial_area' => 'decimal:2',
        'common_area_share' => 'decimal:2',
        'elevator_fee_coefficient' => 'decimal:2',
        'has_basement' => 'boolean',
        'has_storage' => 'boolean',
        'is_commercial' => 'boolean',
        'has_separate_entrance' => 'boolean',
        'apartment_type' => 'string',
        'owned_by_city' => 'boolean',
        'city_total_area' => 'decimal:2',
        'city_apartment_count' => 'integer',
        'city_common_area_share' => 'decimal:2',
    ];

    public function getFloorDisplayAttribute(): string
    {
        return $this->floor == 0 ? 'P' : (string) $this->floor;
    }

    public function getFloorDisplayLongAttribute(): string
    {
        return $this->floor == 0 ? 'Parter' : "Piętro {$this->floor}";
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function getPrimaryOwnerAttribute(): ?Person
    {
        // City apartments don't have individual owners
        if ($this->owned_by_city) {
            return null;
        }

        return $this->people()->wherePivot('is_primary', true)->first();
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'apartment_person')
            ->withPivot('ownership_share', 'is_primary')
            ->withTimestamps();
    }

    public function waterMeters(): HasMany
    {
        return $this->hasMany(WaterMeter::class);
    }

    public function occupancyHistory(): HasMany
    {
        return $this->hasMany(ApartmentOccupancy::class);
    }

    public function getFullNumberAttribute(): string
    {
        $number = $this->building_number
            ? "{$this->building_number}/{$this->apartment_number}"
            : $this->apartment_number;

        // Add city indicator for city apartments
        if ($this->owned_by_city) {
            $count = $this->city_apartment_count > 1 ? " ({$this->city_apartment_count} lokali)" : "";
            return "{$number} [Miasto]{$count}";
        }

        return $number;
    }

    // City ownership methods
    public function isCityOwned(): bool
    {
        return $this->owned_by_city;
    }

    public function isCityGroup(): bool
    {
        return $this->owned_by_city && $this->city_apartment_count > 1;
    }

    public function getEffectiveAreaAttribute(): ?float
    {
        // For city apartments, use individual area if available, otherwise city_total_area
        if ($this->owned_by_city && !$this->area && $this->city_total_area) {
            return $this->city_total_area;
        }

        return $this->area;
    }

    public function getEffectiveCommonAreaShareAttribute(): ?float
    {
        // For city apartments, use city_common_area_share if available
        if ($this->owned_by_city && $this->city_common_area_share) {
            return $this->city_common_area_share;
        }

        return $this->common_area_share;
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->owned_by_city) {
            if ($this->city_apartment_count > 1) {
                return "Lokale miasta ({$this->city_apartment_count} szt.) - {$this->full_number}";
            }
            return "Lokal miasta - {$this->full_number}";
        }

        return $this->full_number;
    }

    // Scopes
    public function scopeCityOwned($query)
    {
        return $query->where('owned_by_city', true);
    }

    public function scopePrivatelyOwned($query)
    {
        return $query->where('owned_by_city', false);
    }

    public function scopeForCommunity($query, $communityId)
    {
        return $query->where('community_id', $communityId);
    }

    // Validation rules for city apartments
    public function getCityApartmentValidationRules(): array
    {
        return [
            'owned_by_city' => 'boolean',
            'city_total_area' => 'nullable|numeric|min:0|required_if:owned_by_city,true',
            'city_apartment_count' => 'nullable|integer|min:1|required_if:owned_by_city,true',
            'city_common_area_share' => 'nullable|numeric|min:0|required_if:owned_by_city,true',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($apartment) {
            // Validation for city apartments
            if ($apartment->owned_by_city) {
                // If it's a city apartment, ensure we have either individual area or city_total_area
                if (!$apartment->area && !$apartment->city_total_area) {
                    throw new \InvalidArgumentException('City apartments must have either individual area or city_total_area');
                }

                // If city_apartment_count is set, it should be at least 1
                if ($apartment->city_apartment_count && $apartment->city_apartment_count < 1) {
                    $apartment->city_apartment_count = 1;
                }

                // Set default city_apartment_count if not specified
                if ($apartment->owned_by_city && !$apartment->city_apartment_count) {
                    $apartment->city_apartment_count = 1;
                }
            }
        });
    }
}
