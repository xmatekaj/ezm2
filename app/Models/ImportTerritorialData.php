<?php
// app/Models/TerritorialUnit.php

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

// app/Models/Street.php

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
                    ->where('gmi', $cityCode)
                    ->orderBy('nazwa_1')
                    ->orderBy('nazwa_2')
                    ->get();
    }
}

// app/Console/Commands/ImportTerritorialData.php

namespace App\Console\Commands;

use App\Models\TerritorialUnit;
use App\Models\Street;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTerritorialData extends Command
{
    protected $signature = 'import:territorial-data {units_file} {streets_file}';
    protected $description = 'Import territorial units and streets data from CSV files';

    public function handle()
    {
        $unitsFile = $this->argument('units_file');
        $streetsFile = $this->argument('streets_file');

        if (!file_exists($unitsFile) || !file_exists($streetsFile)) {
            $this->error('One or both files do not exist.');
            return 1;
        }

        $this->info('Importing territorial units...');
        $this->importTerritorialUnits($unitsFile);

        $this->info('Importing streets...');
        $this->importStreets($streetsFile);

        $this->info('Import completed successfully!');
        return 0;
    }

    private function importTerritorialUnits($file)
    {
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 0, ';'); // Skip header
        
        DB::transaction(function () use ($handle) {
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                TerritorialUnit::updateOrCreate(
                    [
                        'woj' => $data[0],
                        'pow' => $data[1] ?: null,
                        'gmi' => $data[2] ?: null,
                        'rodz' => $data[3] ?: null,
                    ],
                    [
                        'nazwa' => $data[4],
                        'nazwa_dod' => $data[5],
                        'stan_na' => $data[6],
                    ]
                );
            }
        });
        
        fclose($handle);
    }

    private function importStreets($file)
    {
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 0, ';'); // Skip header
        
        DB::transaction(function () use ($handle) {
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                Street::updateOrCreate(
                    [
                        'woj' => $data[0],
                        'pow' => $data[1],
                        'gmi' => $data[2],
                        'sym' => $data[4],
                        'sym_ul' => $data[5],
                    ],
                    [
                        'rodz_gmi' => $data[3],
                        'cecha' => $data[6],
                        'nazwa_1' => $data[7],
                        'nazwa_2' => $data[8] ?: null,
                        'stan_na' => $data[9],
                    ]
                );
            }
        });
        
        fclose($handle);
    }
}