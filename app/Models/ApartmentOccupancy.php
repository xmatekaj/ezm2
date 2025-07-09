<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApartmentOccupancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'number_of_occupants',
        'change_date',
        'apartment_id'
    ];

    protected $casts = [
        'change_date' => 'date',
    ];

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function getPreviousOccupancy(): ?ApartmentOccupancy
    {
        return $this->apartment
            ->occupancyHistory()
            ->where('change_date', '<', $this->change_date)
            ->orderBy('change_date', 'desc')
            ->first();
    }

    public function getOccupancyChangeAttribute(): ?int
    {
        $previous = $this->getPreviousOccupancy();

        if (!$previous) {
            return null;
        }

        return $this->number_of_occupants - $previous->number_of_occupants;
    }

    public function scopeCurrent($query)
    {
        return $query->where('change_date', '<=', now())
                    ->orderBy('change_date', 'desc');
    }

    public function scopeForApartment($query, $apartmentId)
    {
        return $query->where('apartment_id', $apartmentId);
    }
}
