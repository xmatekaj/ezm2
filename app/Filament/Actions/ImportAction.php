<?php

namespace App\Filament\Actions;

use App\Services\Import\ImportManager;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ImportAction
{
    public static function make(string $type, string $label): Action
    {
        return Action::make("import_{$type}")
            ->label($label)
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->form([
                Components\Section::make("Import: {$label}")
                    ->schema([
                        Components\FileUpload::make('csv_file')
                            ->label('Plik CSV')
                            ->required()
                            ->acceptedFileTypes(['text/csv', 'application/csv', '.csv'])
                            ->maxSize(10240)
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private'),

                        Components\TextInput::make('delimiter')
                            ->label('Separator CSV')
                            ->default(',')
                            ->maxLength(1),

                        Components\Toggle::make('skip_header')
                            ->label('Pomiń nagłówki')
                            ->default(true),
                    ]),
            ])
            ->action(function (array $data) use ($type, $label) {
                try {
                    $importManager = app(ImportManager::class);
                    $filePath = Storage::disk('local')->path($data['csv_file']);

                    $options = [
                        'delimiter' => $data['delimiter'],
                        'skip_header' => $data['skip_header'],
                    ];

                    $stats = $importManager->import($type, $filePath, $options);

                    Storage::disk('local')->delete($data['csv_file']);

                    Notification::make()
                        ->title("Import {$label} zakończony!")
                        ->body("Zaimportowano {$stats['successful_imports']} rekordów")
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    if (isset($data['csv_file'])) {
                        Storage::disk('local')->delete($data['csv_file']);
                    }

                    Notification::make()
                        ->title("Błąd importu: {$label}")
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->requiresConfirmation();
    }

    public static function downloadTemplate(string $type, string $label): Action
	{
		return Action::make("download_template_{$type}")
			->label('Pobierz szablon CSV')
			->icon('heroicon-o-document-arrow-down')
			->color('info')
			->action(function () use ($type) {
				$content = match($type) {
					'communities' => 
						// Headers (must match column mapping order)
						"name,full_name,address_street,address_postal_code,address_city,address_state,regon,tax_id,manager_name,manager_address_street,manager_address_postal_code,manager_address_city,common_area_size,apartments_area,apartment_count,has_elevator\n" .
						// Sample data
						"\"WM Słoneczna\",\"Wspólnota Mieszkaniowa przy ul. Słonecznej\",\"ul. Słoneczna 15\",\"40-001\",\"Katowice\",\"śląskie\",\"123456789\",\"1234567890\",\"Zarządca ABC Sp. z o.o.\",\"ul. Zarządu 1\",\"40-002\",\"Katowice\",\"250.50\",\"1500.75\",\"24\",\"tak\"\n" .
						"\"WM Testowa\",\"Wspólnota Mieszkaniowa Testowa\",\"ul. Testowa 5\",\"40-003\",\"Katowice\",\"śląskie\",\"987654321\",\"0987654321\",\"Zarządca XYZ\",\"ul. Administracji 2\",\"40-004\",\"Katowice\",\"180.00\",\"1200.00\",\"18\",\"nie\"",
					default => "No template available for {$type}"
				};

				$filename = "template_{$type}_" . date('Y-m-d') . '.csv';

				return response()->streamDownload(function () use ($content) {
					echo $content;
				}, $filename, [
					'Content-Type' => 'text/csv',
					'Content-Disposition' => "attachment; filename=\"{$filename}\"",
				]);
			});
	}
}