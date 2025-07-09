<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaterMeter extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_date',
        'transmitter_installation_date',
        'meter_expiry_date',
        'transmitter_expiry_date',
        'meter_number',
        'transmitter_number',
        'is_active',
        'apartment_id'
    ];

    protected $casts = [
        'installation_date' => 'date',
        'transmitter_installation_date' => 'date',
        'meter_expiry_date' => 'date',
        'transmitter_expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function waterReadings(): HasMany
    {
        return $this->hasMany(WaterReading::class);
    }

    public function getLatestReadingAttribute(): ?WaterReading
    {
        return $this->waterReadings()->latest('reading_date')->first();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->meter_expiry_date && $this->meter_expiry_date < now();
    }

    public function getIsTransmitterExpiredAttribute(): bool
    {
        return $this->transmitter_expiry_date && $this->transmitter_expiry_date < now();
    }
}
