<?php

namespace App\Filament\Resources\WaterReadingResource\Pages;

use App\Filament\Resources\WaterReadingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWaterReading extends ViewRecord
{
    protected static string $resource = WaterReadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
