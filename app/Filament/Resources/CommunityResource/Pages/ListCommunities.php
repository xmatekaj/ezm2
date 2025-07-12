<?php

namespace App\Filament\Resources\CommunityResource\Pages;

use App\Filament\Resources\CommunityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommunities extends ListRecords
{
    protected static string $resource = CommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label(__('app.action_titles.create.community'))
                ->icon('heroicon-o-plus')
                ->color('success')
                ->url(static::getResource()::getUrl('create')),

            \Filament\Actions\Action::make('download_template')
                ->label(__('app.common.download_csv_template'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    $content = "name,full_name,address_street,address_postal_code,address_city,address_state,regon,tax_id,manager_name,manager_address_street,manager_address_postal_code,manager_address_city,common_area_size,apartments_area,apartment_count,has_elevator\n" .
                              "\"WM Słoneczna\",\"Wspólnota Mieszkaniowa przy ul. Słonecznej\",\"ul. Słoneczna 15\",\"40-001\",\"Katowice\",\"śląskie\",\"123456789\",\"1234567890\",\"Zarządca ABC Sp. z o.o.\",\"ul. Zarządu 1\",\"40-002\",\"Katowice\",\"250.50\",\"1500.75\",\"24\",\"tak\"";

                    $filename = "template_communities_" . date('Y-m-d') . '.csv';

                    return response()->streamDownload(function () use ($content) {
                        echo $content;
                    }, $filename, [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    ]);
                }),

            \Filament\Actions\Action::make('import')
                ->label(__('app.common.import') . ' ' . __('app.communities.plural'))
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
        return __('app.communities.plural');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
