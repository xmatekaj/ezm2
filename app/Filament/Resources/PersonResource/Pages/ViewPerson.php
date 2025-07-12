<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPerson extends ViewRecord
{
    protected static string $resource = PersonResource::class;

    public function getTitle(): string
    {
        return __('app.people.singular');
    }

    // Remove breadcrumbs
    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label(__('app.common.edit'))
                ->icon('heroicon-o-pencil'),
            Actions\DeleteAction::make()
                ->label(__('app.common.delete'))
                ->icon('heroicon-o-trash'),
        ];
    }
}
