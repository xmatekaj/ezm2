<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'is_credit',
        'booking_date',
        'transaction_number',
        'counterparty_details',
        'title',
        'additional_info',
        'notes',
        'person_id',
        'bank_account_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_credit' => 'boolean',
        'booking_date' => 'date',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function getTypeAttribute(): string
    {
        return $this->is_credit ? 'Wpłata' : 'Wydatek';
    }

    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->is_credit ? '+' : '-';
        return $sign . number_format($this->amount, 2, ',', ' ') . ' PLN';
    }

    public function scopeCredits($query)
    {
        return $query->where('is_credit', true);
    }

    public function scopeDebits($query)
    {
        return $query->where('is_credit', false);
    }

    public function scopeForPerson($query, $personId)
    {
        return $query->where('person_id', $personId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('booking_date', [$startDate, $endDate]);
    }
}
