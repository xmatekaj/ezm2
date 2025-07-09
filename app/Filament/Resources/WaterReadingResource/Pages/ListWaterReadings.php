<?php

namespace App\Filament\Resources\WaterReadingResource\Pages;

use App\Filament\Resources\WaterReadingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaterReadings extends ListRecords
{
    protected static string $resource = WaterReadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
