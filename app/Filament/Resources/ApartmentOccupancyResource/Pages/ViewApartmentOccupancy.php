<?php

namespace App\Filament\Resources\ApartmentOccupancyResource\Pages;

use App\Filament\Resources\ApartmentOccupancyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewApartmentOccupancy extends ViewRecord
{
    protected static string $resource = ApartmentOccupancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
