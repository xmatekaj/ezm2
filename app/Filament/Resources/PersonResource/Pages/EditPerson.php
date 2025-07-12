<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerson extends EditRecord
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

    // Header actions inline with title
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(__('app.common.delete'))
                ->icon('heroicon-o-trash'),
        ];
    }

    // Form actions with Save and Cancel
    protected function getFormActions(): array
    {
        return [
            $this->getSaveAction(),
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

    // Custom save action
    protected function getSaveAction(): Actions\Action
    {
        return Actions\Action::make('save')
            ->label(__('app.common.save'))
            ->submit('save')
            ->keyBindings(['mod+s'])
            ->icon('heroicon-o-check');
    }
}
