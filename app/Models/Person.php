<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    use HasFactory;

    protected $table = 'people';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'correspondence_address_street',
        'correspondence_address_postal_code',
        'correspondence_address_city',
        'is_active',
        'notes',
        'ownership_share',
        'spouse_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ownership_share' => 'decimal:2',
    ];

    public function spouse(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'spouse_id');
    }

    public function apartments(): BelongsToMany
    {
        return $this->belongsToMany(Apartment::class, 'apartment_people')
            ->withPivot('ownership_share', 'is_primary')
            ->withTimestamps();
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFullAddressAttribute(): string
    {
        if (!$this->correspondence_address_street) {
            return '';
        }

        return trim("{$this->correspondence_address_street}, {$this->correspondence_address_postal_code} {$this->correspondence_address_city}");
    }

    public function getPrimaryApartmentAttribute(): ?Apartment
    {
        return $this->apartments()->wherePivot('is_primary', true)->first();
    }
}
