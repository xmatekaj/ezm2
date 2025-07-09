<?php

namespace App\Filament\Resources\WaterReadingResource\Pages;

use App\Filament\Resources\WaterReadingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWaterReading extends EditRecord
{
    protected static string $resource = WaterReadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
