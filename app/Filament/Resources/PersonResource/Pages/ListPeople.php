<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeople extends ListRecords
{
    protected static string $resource = PersonResource::class;

    // Hide the default Create button since we moved it to headerActions
// Add Import and Download CSV buttons to PersonResource ListPeople page
// app/Filament/Resources/PersonResource/Pages/ListPeople.php

protected function getHeaderActions(): array
{
    return [
        \Filament\Actions\Action::make('create')
            ->label(__('app.action_titles.create.person'))
            ->icon('heroicon-o-plus')
            ->color('success')
            ->url(static::getResource()::getUrl('create')),

        \Filament\Actions\Action::make('download_template')
            ->label('Pobierz szablon CSV')
            ->icon('heroicon-o-document-arrow-down')
            ->color('info')
            ->action(function () {
                $content = "first_name,last_name,email,phone,correspondence_address_street,correspondence_address_postal_code,correspondence_address_city,ownership_share,notes\n" .
                          "\"Jan\",\"Kowalski\",\"jan.kowalski@example.com\",\"+48 123 456 789\",\"ul. Mieszkańcowa 10/5\",\"40-001\",\"Katowice\",\"100.00\",\"Właściciel mieszkania\"";

                $filename = "template_people_" . date('Y-m-d') . '.csv';

                return response()->streamDownload(function () use ($content) {
                    echo $content;
                }, $filename, [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                ]);
            }),

        \Filament\Actions\Action::make('import')
            ->label('Importuj osoby')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->form([
                \Filament\Forms\Components\FileUpload::make('csv_file')
                    ->label('Plik CSV')
                    ->required()
                    ->acceptedFileTypes(['text/csv', 'application/csv', '.csv'])
                    ->maxSize(10240),
            ])
            ->action(function (array $data) {
                // Simple placeholder - you can implement full import later
                \Filament\Notifications\Notification::make()
                    ->title('Import funkcja')
                    ->body('Import będzie zaimplementowany wkrótce')
                    ->info()
                    ->send();
            }),
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
