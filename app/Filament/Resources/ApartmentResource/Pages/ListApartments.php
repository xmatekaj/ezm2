<?php

namespace App\Filament\Resources\ApartmentResource\Pages;

use App\Filament\Resources\ApartmentResource;
use App\Models\Apartment;
use App\Models\Community;
use App\Services\Import\CsvTemplateGenerator;
use App\Services\Import\ImportManager;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListApartments extends ListRecords
{
    protected static string $resource = ApartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('bulk_create')
                ->label('Utwórz masowo')
                ->icon('heroicon-o-plus-circle')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('community_id')
                        ->label(__('app.common.community'))
                        ->options(Community::all()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),

                    Forms\Components\Repeater::make('entrances')
                        ->label('Klatki schodowe')
                        ->schema([
                            Forms\Components\TextInput::make('building_number')
                                ->label('Numer budynku')
                                ->required(),
                            Forms\Components\TextInput::make('start_number')
                                ->label('Pierwszy numer lokalu')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('end_number')
                                ->label('Ostatni numer lokalu')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('floor_start')
                                ->label('Pierwsze piętro')
                                ->numeric()
                                ->default(0),
                            Forms\Components\TextInput::make('apartments_per_floor')
                                ->label('Lokali na piętro')
                                ->numeric()
                                ->default(4),
                        ])
                        ->columns(5)
                        ->defaultItems(1)
                        ->addActionLabel('Dodaj klatkę'),
                ])
                ->action(function (array $data) {
                    $this->bulkCreateApartments($data);
                }),

            Actions\Action::make('download_template')
                ->label(__('app.common.download_template'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $templateGenerator = app(CsvTemplateGenerator::class);
                    return $templateGenerator->downloadTemplate('apartments', true);
                }),

            Actions\Action::make('import')
                ->label(__('app.common.import') . ' ' . __('app.apartments.plural'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    Forms\Components\Select::make('community_id')
                        ->label(__('app.common.community'))
                        ->options(Community::all()->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->helperText('Wybierz wspólnotę, do której chcesz zaimportować lokale'),

                    Forms\Components\FileUpload::make('csv_file')
                        ->label(__('app.common.csv_file'))
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'application/csv', '.csv'])
                        ->maxSize(10240)
                        ->helperText('Maksymalny rozmiar pliku: 10MB'),

                    Forms\Components\TextInput::make('delimiter')
                        ->label('Separator CSV')
                        ->default(',')
                        ->maxLength(1)
                        ->helperText('Znak separatora kolumn (zwykle , lub ;)'),

                    Forms\Components\Select::make('encoding')
                        ->label('Kodowanie pliku')
                        ->options([
                            'UTF-8' => 'UTF-8',
                            'ISO-8859-1' => 'ISO-8859-1 (Latin-1)',
                            'ISO-8859-2' => 'ISO-8859-2 (Latin-2)',
                            'Windows-1250' => 'Windows-1250',
                        ])
                        ->default('UTF-8')
                        ->helperText('Kodowanie znaków pliku CSV'),

                    Forms\Components\Toggle::make('skip_header')
                        ->label('Pomiń pierwszy wiersz (nagłówek)')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    $this->importApartments($data);
                }),
        ];
    }

    protected function bulkCreateApartments(array $data): void
    {
        $created = 0;

        foreach ($data['entrances'] as $entrance) {
            for ($i = $entrance['start_number']; $i <= $entrance['end_number']; $i++) {
                // Calculate floor based on apartment number
                $floor = $entrance['floor_start'] + intval(($i - $entrance['start_number']) / $entrance['apartments_per_floor']);

                Apartment::create([
                    'community_id' => $data['community_id'],
                    'building_number' => $entrance['building_number'],
                    'apartment_number' => (string) $i,
                    'code' => (string) $i, // Default code same as apartment number
                    'floor' => $floor,
                    'area' => null, // To be filled later
                    'elevator_fee_coefficient' => 1.00,
                    'has_basement' => false,
                    'has_storage' => false,
                    'apartment_type' => 'residential',
                    'is_commercial' => false,
                ]);
                $created++;
            }
        }

        Notification::make()
            ->title("Utworzono {$created} lokali")
            ->success()
            ->send();
    }

    protected function importApartments(array $data): void
    {
        try {
            $uploadedFile = $data['csv_file'];
            $filePath = storage_path('app/' . $uploadedFile);

            $options = [
                'community_id' => $data['community_id'],
                'delimiter' => $data['delimiter'] ?? ',',
                'encoding' => $data['encoding'] ?? 'UTF-8',
                'skip_header' => $data['skip_header'] ?? true,
                'batch_size' => 100,
            ];

            $importManager = app(ImportManager::class);
            $stats = $importManager->import('apartments', $filePath, $options);

            // Clean up uploaded file
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $message = "Import zakończony!\n" .
                      "Przetworzono wierszy: {$stats['processed_rows']}\n" .
                      "Pomyślnie zaimportowano: {$stats['successful_imports']}\n" .
                      "Błędy: {$stats['failed_imports']}";

            if (!empty($stats['errors'])) {
                $message .= "\n\nBłędy:\n" . implode("\n", array_slice($stats['errors'], 0, 5));
                if (count($stats['errors']) > 5) {
                    $message .= "\n... i " . (count($stats['errors']) - 5) . " więcej";
                }
            }

            if ($stats['failed_imports'] > 0) {
                Notification::make()
                    ->title('Import zakończony z błędami')
                    ->body($message)
                    ->warning()
                    ->duration(10000)
                    ->send();
            } else {
                Notification::make()
                    ->title('Import zakończony pomyślnie')
                    ->body($message)
                    ->success()
                    ->send();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Błąd importu')
                ->body('Wystąpił błąd podczas importu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getTitle(): string
    {
        return __('app.apartments.plural');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
