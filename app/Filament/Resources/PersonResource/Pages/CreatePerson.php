<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePerson extends CreateRecord
{
    protected static string $resource = PersonResource::class;

    public function getTitle(): string
    {
        return __('app.action_titles.create.person');
    }

    // Remove breadcrumbs
    public function getBreadcrumbs(): array
    {
        return [];
    }

    // Make header compact and inline
    protected function getHeaderActions(): array
    {
        return [];
    }

    // Override the form actions to add Save and Cancel
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelAction(),
        ];
    }

    protected function getCancelAction(): Actions\Action
    {
        return Actions\Action::make('cancel')
            ->label(__('app.common.cancel'))
            ->url($this->getResource()::getUrl('index'))
            ->color('gray');
    }


}
