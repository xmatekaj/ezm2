<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'user_type',
        'phone',
        'is_active',
        'last_login_at',
        'person_id',
        'two_factor_enabled',
        'two_factor_method',
        'two_factor_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_verified_at' => 'datetime',
        ];
    }

    // Relationships
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class, 'user_communities')
            ->withPivot(['access_type', 'permissions', 'is_active', 'verified_at', 'expires_at'])
            ->withTimestamps();
    }

    public function userCommunities(): HasMany
    {
        return $this->hasMany(UserCommunity::class);
    }

    public function registrationVerifications(): HasMany
    {
        return $this->hasMany(RegistrationVerification::class, 'email', 'email');
    }

    // Helper Methods
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return "{$this->first_name} {$this->last_name}";
        }
        return $this->name ?? $this->email;
    }

    public function isSuperAdmin(): bool
    {
        return $this->user_type === 'super_admin';
    }

    public function isOwner(): bool
    {
        return $this->user_type === 'owner';
    }

    public function hasAccessToCommunity(Community $community): bool
    {
        return $this->communities()
            ->wherePivot('community_id', $community->id)
            ->wherePivot('is_active', true)
            ->exists();
    }

    public function getAccessibleCommunities()
    {
        return $this->communities()
            ->wherePivot('is_active', true)
            ->get();
    }

    public function getOwnedApartments()
    {
        if (!$this->person) {
            return collect([]);
        }

        return $this->person->apartments()->get();
    }

    public function canManageCommunity(Community $community): bool
    {
        // Only super admin can manage communities
        return $this->isSuperAdmin();
    }

    public function canViewApartment(Apartment $apartment): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Regular owners can only see their own apartments
        if ($this->isOwner() && $this->person) {
            return $this->person->apartments()
                ->where('apartments.id', $apartment->id)
                ->exists();
        }

        return false;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOwners($query)
    {
        return $query->where('user_type', 'owner');
    }

    public function scopeForCommunity($query, $communityId)
    {
        return $query->whereHas('communities', function ($q) use ($communityId) {
            $q->where('community_id', $communityId)
              ->wherePivot('is_active', true);
        });
    }
}
