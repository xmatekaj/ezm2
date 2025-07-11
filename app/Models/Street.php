<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Street extends Model
{
    use HasFactory;

    protected $fillable = [
        'woj',
        'pow',
        'gmi',
        'rodz_gmi',
        'sym',
        'sym_ul',
        'cecha',
        'nazwa_1',
        'nazwa_2',
        'stan_na',
    ];

    protected $casts = [
        'stan_na' => 'date',
    ];

    public function territorialUnit(): BelongsTo
    {
        return $this->belongsTo(TerritorialUnit::class, ['woj', 'pow', 'gmi'], ['woj', 'pow', 'gmi']);
    }

    public function getFullNameAttribute(): string
    {
        $name = '';
        if ($this->cecha) {
            $name .= $this->cecha . ' ';
        }
        $name .= $this->nazwa_1;
        if ($this->nazwa_2) {
            $name .= ' ' . $this->nazwa_2;
        }
        return $name;
    }

    public static function getStreetsForCity($voivodeshipCode, $cityCode)
    {
        return static::where('woj', $voivodeshipCode)
                    ->where('pow', $cityCode)  
                    ->orderBy('nazwa_1')
                    ->orderBy('nazwa_2')
                    ->get();
    }
}