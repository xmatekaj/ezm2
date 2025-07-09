<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApartmentPerson extends Model
{
    use HasFactory;

    protected $table = 'apartment_people';

    protected $fillable = [
        'apartment_id',
        'person_id',
        'ownership_share',
        'is_primary'
    ];

    protected $casts = [
        'ownership_share' => 'decimal:2',
        'is_primary' => 'boolean',
    ];

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeOwners($query)
    {
        return $query->whereNotNull('ownership_share');
    }

    public function scopeForApartment($query, $apartmentId)
    {
        return $query->where('apartment_id', $apartmentId);
    }

    public function scopeForPerson($query, $personId)
    {
        return $query->where('person_id', $personId);
    }

    protected static function boot()
    {
        parent::boot();

        // Ensure only one primary person per apartment
        static::saving(function ($apartmentPerson) {
            if ($apartmentPerson->is_primary) {
                // Remove primary status from other records for this apartment
                static::where('apartment_id', $apartmentPerson->apartment_id)
                    ->where('id', '!=', $apartmentPerson->id)
                    ->update(['is_primary' => false]);
            }
        });
    }
}
