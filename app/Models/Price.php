<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'change_date',
        'water_sewage_price',
        'garbage_price',
        'management_fee',
        'renovation_fund',
        'loan_fund',
        'central_heating_advance',
        'community_id'
    ];

    protected $casts = [
        'change_date' => 'date',
        'water_sewage_price' => 'decimal:2',
        'garbage_price' => 'decimal:2',
        'management_fee' => 'decimal:2',
        'renovation_fund' => 'decimal:2',
        'loan_fund' => 'decimal:2',
        'central_heating_advance' => 'decimal:2',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function getTotalMonthlyFeeAttribute(): float
    {
        return $this->garbage_price +
               $this->management_fee +
               $this->renovation_fund +
               $this->loan_fund +
               $this->central_heating_advance;
    }

    public function calculateWaterBill(float $consumption): float
    {
        return $consumption * $this->water_sewage_price;
    }

    public function calculateApartmentFee(Apartment $apartment): float
    {
        $baseFee = $this->getTotalMonthlyFeeAttribute();

        // Calculate based on apartment area or share
        if ($apartment->area) {
            return $baseFee * ($apartment->area / 100); // Example calculation
        }

        if ($apartment->common_area_share) {
            return $baseFee * ($apartment->common_area_share / 100);
        }

        return $baseFee;
    }

    public function scopeCurrent($query)
    {
        return $query->where('change_date', '<=', now())
                    ->orderBy('change_date', 'desc');
    }

    public function scopeForCommunity($query, $communityId)
    {
        return $query->where('community_id', $communityId);
    }
}
