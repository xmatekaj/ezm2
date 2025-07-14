<?php

namespace App\Filament\Resources\SettingsResource\Pages;

use App\Filament\Resources\SettingsResource;
use App\Models\Setting;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageSettings extends ManageRecords
{
    protected static string $resource = SettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manager_settings')
                ->label('Konfiguruj zarządcę')
                ->icon('heroicon-o-user-circle')
                ->color('primary')
                ->form([
                    Forms\Components\Section::make('Dane zarządcy')
                        ->description('Konfiguracja danych zarządcy używanych w całej aplikacji')
                        ->schema([
                            Forms\Components\TextInput::make('manager_name')
                                ->label('Nazwa zarządcy')
                                ->required()
                                ->maxLength(255)
                                ->default(fn () => Setting::get('manager_name', '')),

                            Forms\Components\TextInput::make('manager_address_street')
                                ->label('Ulica zarządcy')
                                ->required()
                                ->maxLength(255)
                                ->default(fn () => Setting::get('manager_address_street', '')),

                            Forms\Components\TextInput::make('manager_address_postal_code')
                                ->label('Kod pocztowy zarządcy')
                                ->required()
                                ->maxLength(10)
                                ->placeholder('00-000')
                                ->default(fn () => Setting::get('manager_address_postal_code', '')),

                            Forms\Components\TextInput::make('manager_address_city')
                                ->label('Miasto zarządcy')
                                ->required()
                                ->maxLength(255)
                                ->default(fn () => Setting::get('manager_address_city', '')),
                        ])->columns(2),
                ])
                ->action(function (array $data): void {
                    Setting::set('manager_name', $data['manager_name']);
                    Setting::set('manager_address_street', $data['manager_address_street']);
                    Setting::set('manager_address_postal_code', $data['manager_address_postal_code']);
                    Setting::set('manager_address_city', $data['manager_address_city']);
                    
                    // Mark app as initialized if not already
                    if (!Setting::get('app_initialized', false)) {
                        Setting::set('app_initialized', true, 'boolean');
                    }

                    Notification::make()
                        ->title('Dane zarządcy zostały zaktualizowane')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reset_app')
                ->label('Reset aplikacji')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Resetuj aplikację')
                ->modalDescription('Ta akcja spowoduje reset ustawień aplikacji. Czy na pewno chcesz kontynuować?')
                ->modalSubmitActionLabel('Reset')
                ->action(function (): void {
                    Setting::set('app_initialized', false, 'boolean');
                    Setting::set('manager_name', '');
                    Setting::set('manager_address_street', '');
                    Setting::set('manager_address_postal_code', '');
                    Setting::set('manager_address_city', '');

                    Notification::make()
                        ->title('Aplikacja została zresetowana')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTitle(): string
    {
        return 'Ustawienia aplikacji';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SettingsResource\Widgets\ManagerOverview::class,
        ];
    }
}