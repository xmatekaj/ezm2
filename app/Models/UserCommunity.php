<?php
// app/Models/UserCommunity.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCommunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'community_id',
        'access_type',
        'permissions',
        'is_active',
        'verified_at',
        'expires_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function isActive(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeOwnerAccess($query)
    {
        return $query->where('access_type', 'owner');
    }
}

// app/Models/RegistrationVerification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RegistrationVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'community_id',
        'apartment_id',
        'last_water_settlement_amount',
        'last_fee_amount',
        'last_water_prediction_amount',
        'current_occupants',
        'apartment_number',
        'is_verified',
        'verified_at',
        'expires_at',
        'verification_token',
    ];

    protected $casts = [
        'last_water_settlement_amount' => 'decimal:2',
        'last_fee_amount' => 'decimal:2',
        'last_water_prediction_amount' => 'decimal:2',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function isValid(): bool
    {
        return !$this->is_verified && !$this->isExpired();
    }

    public function verify(): bool
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return true;
    }

    public function scopeValid($query)
    {
        return $query->where('is_verified', false)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($verification) {
            if (empty($verification->verification_token)) {
                $verification->verification_token = Str::random(64);
            }

            if (empty($verification->expires_at)) {
                $verification->expires_at = now()->addDays(7); // 7 days to complete registration
            }
        });
    }
}