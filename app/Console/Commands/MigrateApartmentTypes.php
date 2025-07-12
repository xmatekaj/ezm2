<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apartment;
use App\Enums\ApartmentType;

class MigrateApartmentTypes extends Command
{
    protected $signature = 'apartments:migrate-types {--dry-run : Show what would be changed without making changes}';
    protected $description = 'Migrate apartment types from boolean is_commercial to enum apartment_type';

    public function handle()
    {
        $apartments = Apartment::all();
        $changes = [];

        foreach ($apartments as $apartment) {
            $newType = $apartment->is_commercial
                ? ApartmentType::COMMERCIAL->value
                : ApartmentType::RESIDENTIAL->value;

            if ($apartment->apartment_type !== $newType) {
                $changes[] = [
                    'id' => $apartment->id,
                    'number' => $apartment->full_number,
                    'current' => $apartment->apartment_type,
                    'new' => $newType
                ];

                if (!$this->option('dry-run')) {
                    $apartment->update(['apartment_type' => $newType]);
                }
            }
        }

        if (empty($changes)) {
            $this->info('No apartments need type migration.');
            return 0;
        }

        $this->table(
            ['ID', 'Number', 'Current Type', 'New Type'],
            $changes
        );

        if ($this->option('dry-run')) {
            $this->info('This was a dry run. Use --no-dry-run to make actual changes.');
        } else {
            $this->info(count($changes) . ' apartments updated successfully.');
        }

        return 0;
    }
}
