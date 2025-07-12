<?php

namespace App\Filament\Resources\ApartmentResource\Pages;

use App\Filament\Resources\ApartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApartments extends ListRecords
{
    protected static string $resource = ApartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label(__('app.action_titles.create.apartment'))
                ->icon('heroicon-o-plus')
                ->color('success')
                ->url(static::getResource()::getUrl('create')),

            \Filament\Actions\Action::make('download_template')
                ->label(__('app.common.download_csv_template'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    $content = "community_name,building_number,apartment_number,area,basement_area,storage_area,heated_area,common_area_share,floor,elevator_fee_coefficient,has_basement,has_storage,is_owned,is_commercial\n" .
                              "\"WM Słoneczna\",\"1\",\"1\",\"45.50\",\"3.20\",\"2.50\",\"45.50\",\"4.25\",\"0\",\"1.00\",\"tak\",\"tak\",\"tak\",\"nie\"";

                    $filename = "template_apartments_" . date('Y-m-d') . '.csv';

                    return response()->streamDownload(function () use ($content) {
                        echo $content;
                    }, $filename, [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    ]);
                }),

            \Filament\Actions\Action::make('import')
                ->label(__('app.common.import') . ' ' . __('app.apartments.plural'))
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
        return __('app.apartments.plural');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
