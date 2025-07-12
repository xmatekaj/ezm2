<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeople extends ListRecords
{
    protected static string $resource = PersonResource::class;

    // Hide the default Create button since we moved it to headerActions
    protected function getHeaderActions(): array
    {
        return [
            // Empty - actions are now in table headerActions
        ];
    }

    // Override the title to remove resource name
    public function getTitle(): string
    {
        return __('app.people.plural');
    }

    // Remove breadcrumbs
    public function getBreadcrumbs(): array
    {
        return [];
    }
}
