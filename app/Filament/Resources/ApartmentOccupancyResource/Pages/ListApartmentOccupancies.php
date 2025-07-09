<?php

namespace App\Filament\Resources\ApartmentOccupancyResource\Pages;

use App\Filament\Resources\ApartmentOccupancyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApartmentOccupancies extends ListRecords
{
    protected static string $resource = ApartmentOccupancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
