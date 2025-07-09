<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'reading',
        'reading_date',
        'reverse_flow_alarm',
        'magnet_alarm',
        'water_meter_id'
    ];

    protected $casts = [
        'reading' => 'decimal:2',
        'reading_date' => 'datetime',
        'reverse_flow_alarm' => 'boolean',
        'magnet_alarm' => 'boolean',
    ];

    public function waterMeter(): BelongsTo
    {
        return $this->belongsTo(WaterMeter::class);
    }

    public function getHasAlarmsAttribute(): bool
    {
        return $this->reverse_flow_alarm || $this->magnet_alarm;
    }

    public function getPreviousReading(): ?WaterReading
    {
        return $this->waterMeter
            ->waterReadings()
            ->where('reading_date', '<', $this->reading_date)
            ->latest('reading_date')
            ->first();
    }

    public function getConsumptionAttribute(): ?float
    {
        $previous = $this->getPreviousReading();

        if (!$previous) {
            return null;
        }

        return $this->reading - $previous->reading;
    }
}
