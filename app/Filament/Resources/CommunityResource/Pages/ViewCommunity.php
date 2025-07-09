<?php
// app/Filament/Resources/CommunityResource/Pages/ViewCommunity.php

namespace App\Filament\Resources\CommunityResource\Pages;

use App\Filament\Resources\CommunityResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCommunity extends ViewRecord
{
    protected static string $resource = CommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
