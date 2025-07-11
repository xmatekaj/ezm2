<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerritorialUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'woj',
        'pow',
        'gmi',
        'rodz',
        'nazwa',
        'nazwa_dod',
        'stan_na',
    ];

    protected $casts = [
        'stan_na' => 'date',
    ];

    public function streets(): HasMany
    {
        return $this->hasMany(Street::class, ['woj', 'pow', 'gmi'], ['woj', 'pow', 'gmi']);
    }

    public function getFullNameAttribute(): string
    {
        return $this->nazwa . ($this->nazwa_dod ? ' (' . $this->nazwa_dod . ')' : '');
    }

    // Scopes
    public function scopeVoivodeships($query)
    {
        return $query->whereNull('pow')
                    ->whereNull('gmi')
                    ->whereNull('rodz')
                    ->where('nazwa_dod', 'województwo');
    }

    public function scopeCities($query, $voivodeshipCode)
    {
        return $query->where('woj', $voivodeshipCode)
                    ->whereNotNull('pow')
                    ->whereNotNull('gmi')
                    ->whereIn('rodz', ['1', '2', '3']) // gmina miejska, wiejska, miejsko-wiejska
                    ->where('nazwa_dod', 'like', '%gmina%');
    }

    public static function getVoivodeships()
    {
        return static::voivodeships()
                    ->orderBy('nazwa')
                    ->get();
    }

    public static function getCitiesForVoivodeship($voivodeshipCode)
    {
        return static::cities($voivodeshipCode)
                    ->orderBy('nazwa')
                    ->get();
    }
}