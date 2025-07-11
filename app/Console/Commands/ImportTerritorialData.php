<?php

namespace App\Console\Commands;

use App\Models\TerritorialUnit;
use App\Models\Street;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTerritorialData extends Command
{
    protected $signature = 'app:import-territorial-data {units_file} {streets_file} {--batch-size=500} {--skip-units} {--skip-streets}';
    protected $description = 'Import territorial units and streets data from CSV files';

    public function handle()
    {
        $unitsFile = $this->argument('units_file');
        $streetsFile = $this->argument('streets_file');
        $batchSize = (int) $this->option('batch-size');
        $skipUnits = $this->option('skip-units');
        $skipStreets = $this->option('skip-streets');

        if (!$skipUnits && !file_exists($unitsFile)) {
            $this->error("Units file does not exist: {$unitsFile}");
            return 1;
        }

        if (!$skipStreets && !file_exists($streetsFile)) {
            $this->error("Streets file does not exist: {$streetsFile}");
            return 1;
        }

        $this->info("Using batch size: {$batchSize}");

        if (!$skipUnits) {
            $unitsCount = TerritorialUnit::count();
            if ($unitsCount > 0) {
                if (!$this->confirm("Territorial units table already has {$unitsCount} records. Do you want to continue? (This will update existing records)")) {
                    $this->info('Skipping territorial units...');
                    $skipUnits = true;
                }
            }

            if (!$skipUnits) {
                $this->info('Importing territorial units...');
                $unitsCount = $this->importTerritorialUnits($unitsFile, $batchSize);
                $this->info("Processed {$unitsCount} territorial units.");
            }
        } else {
            $this->info('Skipping territorial units (--skip-units flag)');
        }

        if (!$skipStreets) {
            $streetsCount = Street::count();
            if ($streetsCount > 0) {
                if (!$this->confirm("Streets table already has {$streetsCount} records. Do you want to continue? (This will update existing records)")) {
                    $this->info('Skipping streets...');
                    return 0;
                }
            }

            $this->info('Importing streets...');
            $streetsCount = $this->importStreets($streetsFile, $batchSize);
            $this->info("Processed {$streetsCount} streets.");
        } else {
            $this->info('Skipping streets (--skip-streets flag)');
        }

        $this->info('Import completed successfully!');
        return 0;
    }

    private function importTerritorialUnits($file, $batchSize)
    {
        $count = 0;
        $batch = [];
        $handle = fopen($file, 'r');

        // Skip header line
        fgetcsv($handle, 0, ';');

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            // Skip empty lines
            if (empty($data[0])) continue;

            $batch[] = [
                'woj' => $data[0],
                'pow' => $data[1] ?: null,
                'gmi' => $data[2] ?: null,
                'rodz' => $data[3] ?: null,
                'nazwa' => $data[4],
                'nazwa_dod' => $data[5] ?? null,
                'stan_na' => $data[6] ?? now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $count++;

            // Process batch when it reaches the batch size
            if (count($batch) >= $batchSize) {
                $this->processTerritorialUnitsBatch($batch);
                $batch = [];
                $this->info("Processed {$count} territorial units...");
            }
        }

        // Process remaining batch
        if (!empty($batch)) {
            $this->processTerritorialUnitsBatch($batch);
        }

        fclose($handle);
        return $count;
    }

    private function processTerritorialUnitsBatch($batch)
    {
        DB::transaction(function () use ($batch) {
            foreach ($batch as $unit) {
                TerritorialUnit::updateOrCreate(
                    [
                        'woj' => $unit['woj'],
                        'pow' => $unit['pow'],
                        'gmi' => $unit['gmi'],
                        'rodz' => $unit['rodz'],
                    ],
                    [
                        'nazwa' => $unit['nazwa'],
                        'nazwa_dod' => $unit['nazwa_dod'],
                        'stan_na' => $unit['stan_na'],
                    ]
                );
            }
        });
    }

    private function importStreets($file, $batchSize)
    {
        $count = 0;
        $batch = [];
        $handle = fopen($file, 'r');

        // Skip header line
        fgetcsv($handle, 0, ';');

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            // Skip empty lines
            if (empty($data[0]) || empty($data[4])) continue;

            $batch[] = [
                'woj' => $data[0],
                'pow' => $data[1],
                'gmi' => $data[2],
                'rodz_gmi' => $data[3] ?? null,
                'sym' => $data[4],
                'sym_ul' => $data[5],
                'cecha' => $data[6] ?? null,
                'nazwa_1' => $data[7],
                'nazwa_2' => $data[8] ?? null,
                'stan_na' => $data[9] ?? now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $count++;

            // Process batch when it reaches the batch size
            if (count($batch) >= $batchSize) {
                $this->processStreetsBatch($batch);
                $batch = [];
                $this->info("Processed {$count} streets...");

                // Add a small delay to prevent overwhelming the database
                usleep(10000); // 10ms delay
            }
        }

        // Process remaining batch
        if (!empty($batch)) {
            $this->processStreetsBatch($batch);
        }

        fclose($handle);
        return $count;
    }

    private function processStreetsBatch($batch)
    {
        DB::transaction(function () use ($batch) {
            foreach ($batch as $street) {
                Street::updateOrCreate(
                    [
                        'woj' => $street['woj'],
                        'pow' => $street['pow'],
                        'gmi' => $street['gmi'],
                        'sym' => $street['sym'],
                        'sym_ul' => $street['sym_ul'],
                        'nazwa_1' => $street['nazwa_1'],
                        'nazwa_2' => $street['nazwa_2'],
                    ],
                    [
                        'rodz_gmi' => $street['rodz_gmi'],
                        'cecha' => $street['cecha'],
                        'stan_na' => $street['stan_na'],
                    ]
                );
            }
        });
    }
}
