<?php

namespace App\Filament\Resources\ApartmentResource\Pages;

use App\Filament\Resources\ApartmentResource;
use App\Models\Community;
use App\Models\Apartment;
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
        Actions\Action::make('create')
            ->label(__('app.action_titles.create.apartment'))
            ->icon('heroicon-o-plus')
            ->color('success')
            ->url(static::getResource()::getUrl('create')),

        Actions\Action::make('bulk_create')
            ->label('Masowe tworzenie lokali')
            ->icon('heroicon-o-squares-plus')
            ->color('info')
            ->form([
                Forms\Components\Select::make('community_id')
                    ->label('Wspólnota')
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
                            ->label('Numer początkowy')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('end_number')
                            ->label('Numer końcowy')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('floor_start')
                            ->label('Piętro początkowe')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('apartments_per_floor')
                            ->label('Lokali na piętro')
                            ->numeric()
                            ->default(4),
                    ])
                    ->columns(2)
                    ->defaultItems(1),
            ])
            ->action(function (array $data) {
                $this->bulkCreateApartments($data);
            }),

        Actions\Action::make('download_template')
            ->label(__('app.common.download_csv_template'))
            ->icon('heroicon-o-document-arrow-down')
            ->color('info')
            ->action(function () {
                $content = "community_name,building_number,apartment_number,area,basement_area,storage_area,common_area_share,floor,elevator_fee_coefficient,has_basement,has_storage,is_owned,is_commercial\n" .
                          "\"WM Słoneczna\",\"1\",\"1\",\"45.50\",\"3.20\",\"2.50\",\"4.25\",\"0\",\"1.00\",\"tak\",\"tak\",\"tak\",\"nie\"";

                $filename = "template_apartments_" . date('Y-m-d') . '.csv';

                return response()->streamDownload(function () use ($content) {
                    echo $content;
                }, $filename, [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                ]);
            }),

        Actions\Action::make('import')
            ->label(__('app.common.import') . ' ' . __('app.apartments.plural'))
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->form([
                Forms\Components\FileUpload::make('csv_file')
                    ->label(__('app.common.csv_file'))
                    ->required()
                    ->acceptedFileTypes(['text/csv', 'application/csv', '.csv'])
                    ->maxSize(10240),
            ])
            ->action(function (array $data) {
                Notification::make()
                    ->title(__('app.common.import_function'))
                    ->body(__('app.common.import_will_be_implemented_soon'))
                    ->info()
                    ->send();
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
                'is_owned' => true,
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

    public function getTitle(): string
    {
        return __('app.apartments.plural');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
