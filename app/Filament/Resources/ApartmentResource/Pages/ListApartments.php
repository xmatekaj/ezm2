<?php

namespace App\Filament\Resources\ApartmentResource\Pages;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Filament\Resources\ApartmentResource;
use App\Models\Apartment;
use App\Models\Community;
use App\Services\Import\CsvTemplateGenerator;
use App\Services\Import\ImportManager;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

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
        // Generate CSV content without community_id
        $headers = [
            'apartment_number',
            'building_number',
            'code',
            'intercom_code',
            'land_mortgage_register',
            'area',
            'basement_area',
            'storage_area',
            'common_area_share',
            'floor',
            'elevator_fee_coefficient',
            'has_basement',
            'has_storage',
            'apartment_type',
            'usage_description',
            'has_separate_entrance',
            'commercial_area'
        ];

        $sampleData = [
            [
                '1', '15', '1', '1', 'KA1K/12345678/9', '58.50', '5.20', '3.00',
                '5.50', '0', '1.00', 'tak', 'tak', 'residential', '', 'nie', ''
            ],
            [
                '2', '15', '2', '2', 'KA1K/12345678/10', '62.30', '', '',
                '5.85', '1', '1.20', 'nie', 'nie', 'residential', '', 'nie', ''
            ],
            [
                'U1', '15', 'U1', 'U1', 'KA1K/12345678/11', '85.20', '', '',
                '8.00', '0', '0.50', 'nie', 'nie', 'commercial', 'Sklep spożywczy', 'tak', '85.20'
            ]
        ];

        $content = implode(',', $headers) . "\n";
        foreach ($sampleData as $row) {
            $content .= '"' . implode('","', $row) . '"' . "\n";
        }

        $filename = "template_apartments_" . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }),

           Actions\Action::make('import')
    ->label(__('app.import.apartment.import_apartments'))
    ->icon('heroicon-o-arrow-up-tray')
    ->color('warning')
    ->form([
        Forms\Components\Section::make(__('app.import.form.import_settings'))
            ->description(__('app.import.apartment.import_description'))
            ->schema([
                Forms\Components\Select::make('community_id')
                    ->label(__('app.import.apartment.select_community'))
                    ->options(Community::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->helperText(__('app.import.apartment.community_help')),

                // SIMPLIFIED FILE UPLOAD - no fancy Filament processing
                Forms\Components\FileUpload::make('csv_file')
                    ->label(__('app.import.form.select_file'))
                    ->required()
                    ->acceptedFileTypes(['text/csv', '.csv'])
                    ->maxSize(10240)
                    ->disk('local')
                    ->directory('apartment-imports')  // Fixed directory
                    ->preserveFilenames()  // Keep original filename
                    ->visibility('private'),

                Forms\Components\Section::make(__('app.import.form.format_settings'))
                    ->schema([
                        Forms\Components\Select::make('delimiter')
                            ->label(__('app.import.form.delimiter'))
                            ->options([
                                ',' => __('app.import.form.comma_standard'),
                                ';' => __('app.import.form.semicolon_polish'),
                                "\t" => __('app.import.form.tab_excel'),
                                '|' => __('app.import.form.pipe_custom'),
                            ])
                            ->default(';')
                            ->helperText(__('app.import.form.delimiter_help')),

                        Forms\Components\Select::make('encoding')
                            ->label(__('app.import.form.encoding'))
                            ->options([
                                'UTF-8' => __('app.import.form.utf8_universal'),
                                'ISO-8859-2' => __('app.import.form.iso_central_europe'),
                                'Windows-1250' => __('app.import.form.windows_polish'),
                            ])
                            ->default('UTF-8')
                            ->helperText(__('app.import.form.encoding_help')),

                        Forms\Components\Toggle::make('skip_header')
                            ->label(__('app.import.form.skip_header'))
                            ->default(true),
                    ])->columns(3),
            ]),
    ])
    ->action(function (array $data) {
        $this->importApartmentsSimple($data);
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

    protected function importApartmentsSimple(array $data): void
{
    try {
        $uploadedFile = $data['csv_file'];

        \Log::info('Simple import started', [
            'uploaded_file' => $uploadedFile,
            'type' => gettype($uploadedFile),
            'data_keys' => array_keys($data)
        ]);

        // Handle array (Filament sometimes returns arrays)
        if (is_array($uploadedFile)) {
            $uploadedFile = $uploadedFile[0] ?? null;
        }

        if (!$uploadedFile) {
            throw new \Exception('No file was uploaded');
        }

        // Try the straightforward path first
        $filePath = storage_path('app/apartment-imports/' . basename($uploadedFile));

        \Log::info('Trying direct path', [
            'path' => $filePath,
            'exists' => file_exists($filePath)
        ]);

        if (!file_exists($filePath)) {
            // Try Storage disk path
            $filePath = Storage::disk('local')->path('apartment-imports/' . basename($uploadedFile));
            \Log::info('Trying storage disk path', [
                'path' => $filePath,
                'exists' => file_exists($filePath)
            ]);
        }

        if (!file_exists($filePath)) {
            // Read from Storage and create temp file
            $storageKey = 'apartment-imports/' . basename($uploadedFile);

            if (Storage::disk('local')->exists($storageKey)) {
                $content = Storage::disk('local')->get($storageKey);
                $tempFile = tempnam(sys_get_temp_dir(), 'apartment_import_');
                file_put_contents($tempFile, $content);
                $filePath = $tempFile;
                $isTemporary = true;

                \Log::info('Created temp file from storage', [
                    'storage_key' => $storageKey,
                    'temp_file' => $tempFile,
                    'content_size' => strlen($content)
                ]);
            } else {
                throw new \Exception("Could not locate uploaded file: {$uploadedFile}");
            }
        }

        $options = [
            'community_id' => $data['community_id'],
            'delimiter' => $data['delimiter'] ?? ';',
            'encoding' => $data['encoding'] ?? 'UTF-8',
            'skip_header' => $data['skip_header'] ?? true,
            'batch_size' => 100,
        ];

        \Log::info('Starting import with options', $options);

        $importManager = app(ImportManager::class);
        $stats = $importManager->import('apartments', $filePath, $options);

        // Clean up
        if (isset($isTemporary) && $isTemporary && file_exists($filePath)) {
            unlink($filePath);
        }

        // Clean up uploaded file
        try {
            Storage::disk('local')->delete('apartment-imports/' . basename($uploadedFile));
        } catch (\Exception $e) {
            \Log::warning('Could not clean up uploaded file: ' . $e->getMessage());
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
        \Log::error('Simple import failed', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        Notification::make()
            ->title('Błąd importu')
            ->body('Wystąpił błąd podczas importu: ' . $e->getMessage())
            ->danger()
            ->send();
    }
}


    protected function importApartments(array $data): void
{
    try {
        $uploadedFile = $data['csv_file'];

        // FIX: Get the correct file path for Filament uploads
        // Filament stores files in storage/app/livewire-tmp/ or similar
        $filePath = Storage::disk('local')->path($uploadedFile);

        // Alternative method if the above doesn't work:
        // $filePath = storage_path('app/' . $uploadedFile);

        \Log::info('File upload debug', [
            'uploadedFile' => $uploadedFile,
            'constructed_path' => $filePath,
            'file_exists' => file_exists($filePath)
        ]);

        if (!file_exists($filePath)) {
            // Try alternative path construction
            $alternativePath = storage_path('app/livewire-tmp/' . basename($uploadedFile));
            \Log::info('Trying alternative path', [
                'alternative_path' => $alternativePath,
                'alternative_exists' => file_exists($alternativePath)
            ]);

            if (file_exists($alternativePath)) {
                $filePath = $alternativePath;
            } else {
                throw new \Exception("File not found. Tried paths: {$filePath}, {$alternativePath}");
            }
        }

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
