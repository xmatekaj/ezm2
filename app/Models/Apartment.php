<?php

namespace App\Models;

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
        'area',
        'basement_area',
        'storage_area',
        'common_area_share',
        'floor',
        'elevator_fee_coefficient',
        'has_basement',
        'has_storage',
        'is_commercial',
        'community_id'
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'basement_area' => 'decimal:2',
        'storage_area' => 'decimal:2',
        'heated_area' => 'decimal:2',
        'common_area_share' => 'decimal:2',
        'elevator_fee_coefficient' => 'decimal:2',
        'has_basement' => 'boolean',
        'has_storage' => 'boolean',
        'is_commercial' => 'boolean',
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
        return $this->building_number
            ? "{$this->building_number}/{$this->apartment_number}"
            : $this->apartment_number;
    }

    public function getTypeDisplayAttribute(): string
    {
        return ApartmentType::from($this->apartment_type)->label();
    }

    public function isResidential(): bool
    {
        return $this->apartment_type === ApartmentType::RESIDENTIAL->value;
    }

    public function isCommercial(): bool
    {
        return in_array($this->apartment_type, [
            ApartmentType::COMMERCIAL->value,
            ApartmentType::MIXED->value
        ]);
    }

    public function isStorage(): bool
    {
        return $this->apartment_type === ApartmentType::STORAGE->value;
    }
}
