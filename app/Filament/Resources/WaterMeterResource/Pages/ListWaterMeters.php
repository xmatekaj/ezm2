<?php

namespace App\Filament\Resources\WaterMeterResource\Pages;

use App\Filament\Resources\WaterMeterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaterMeters extends ListRecords
{
    protected static string $resource = WaterMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label(__('app.action_titles.create.water_meter'))
                ->icon('heroicon-o-plus')
                ->color('success')
                ->url(static::getResource()::getUrl('create')),

            \Filament\Actions\Action::make('download_template')
                ->label(__('app.common.download_csv_template'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    $content = "community_name,apartment_number,meter_number,transmitter_number,installation_date,meter_expiry_date,transmitter_installation_date,transmitter_expiry_date\n" .
                              "\"WM Słoneczna\",\"1\",\"100001\",\"200001\",\"2023-01-15\",\"2029-01-15\",\"2023-01-15\",\"2028-01-15\"";

                    $filename = "template_water_meters_" . date('Y-m-d') . '.csv';

                    return response()->streamDownload(function () use ($content) {
                        echo $content;
                    }, $filename, [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    ]);
                }),

            \Filament\Actions\Action::make('import')
                ->label(__('app.common.import') . ' ' . __('app.water_meters.plural'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('csv_file')
                        ->label(__('app.common.csv_file'))
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'application/csv', '.csv'])
                        ->maxSize(10240),
                ])
                ->action(function (array $data) {
                    \Filament\Notifications\Notification::make()
                        ->title(__('app.common.import_function'))
                        ->body(__('app.common.import_will_be_implemented_soon'))
                        ->info()
                        ->send();
                }),
        ];
    }

    public function getTitle(): string
    {
        return __('app.water_meters.plural');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
