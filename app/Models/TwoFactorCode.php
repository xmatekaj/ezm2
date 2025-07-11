<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'type',
        'is_used',
        'expires_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }

    public function markAsUsed(): void
    {
        $this->update(['is_used' => true]);
    }

    public function scopeValid($query)
    {
        return $query->where('is_used', false)
                    ->where('expires_at', '>', now());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($code) {
            if (empty($code->expires_at)) {
                $code->expires_at = now()->addMinutes(10); // 10 minutes to use the code
            }
        });
    }
}