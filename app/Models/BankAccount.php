<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'swift',
        'bank_name',
        'address_street',
        'address_postal_code',
        'address_city',
        'is_active',
        'community_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function getFullBankAddressAttribute(): string
    {
        if (!$this->address_street) {
            return $this->bank_name ?? '';
        }

        return trim("{$this->bank_name}, {$this->address_street}, {$this->address_postal_code} {$this->address_city}");
    }

    public function getBalanceAttribute(): float
    {
        $credits = $this->financialTransactions()->where('is_credit', true)->sum('amount');
        $debits = $this->financialTransactions()->where('is_credit', false)->sum('amount');

        return $credits - $debits;
    }

    public function setAccountNumberAttribute($value)
    {
        // Store without spaces to save database space
        $this->attributes['account_number'] = str_replace(' ', '', $value);
    }

    public function getFormattedAccountNumberAttribute(): string
    {
        // Remove all spaces and format as Polish IBAN
        $clean = str_replace(' ', '', $this->account_number);

        if (strlen($clean) === 26) {
            // Format as: XX XXXX XXXX XXXX XXXX XXXX XXXX
            return substr($clean, 0, 2) . ' ' .
                   substr($clean, 2, 4) . ' ' .
                   substr($clean, 6, 4) . ' ' .
                   substr($clean, 10, 4) . ' ' .
                   substr($clean, 14, 4) . ' ' .
                   substr($clean, 18, 4) . ' ' .
                   substr($clean, 22, 4);
        }

        return $this->account_number;
    }
}
