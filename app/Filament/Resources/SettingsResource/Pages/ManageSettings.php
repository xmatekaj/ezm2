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
            Actions\Action::make('configure_settings')
                ->label('Konfiguruj ustawienia')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('primary')
                ->form([
                    Forms\Components\Tabs::make('Settings')
                        ->tabs([
                            // Manager Tab
                            Forms\Components\Tabs\Tab::make('Zarządca')
                                ->icon('heroicon-o-user-circle')
                                ->schema([
                                    Forms\Components\Section::make('Podstawowe dane zarządcy')
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

                                    Forms\Components\Section::make('Identyfikatory')
                                        ->schema([
                                            Forms\Components\TextInput::make('manager_nip')
                                                ->label('NIP zarządcy')
                                                ->maxLength(13)
                                                ->placeholder('000-000-00-00')
                                                ->default(fn () => Setting::get('manager_nip', '')),

                                            Forms\Components\TextInput::make('manager_regon')
                                                ->label('REGON zarządcy')
                                                ->maxLength(14)
                                                ->placeholder('000000000')
                                                ->default(fn () => Setting::get('manager_regon', '')),
                                        ])->columns(2),
                                ]),

                            // Application Tab
                            Forms\Components\Tabs\Tab::make('Aplikacja')
                                ->icon('heroicon-o-computer-desktop')
                                ->schema([
                                    Forms\Components\Section::make('Podstawowe ustawienia aplikacji')
                                        ->schema([
                                            Forms\Components\TextInput::make('app_name')
                                                ->label('Nazwa aplikacji')
                                                ->required()
                                                ->maxLength(255)
                                                ->default(fn () => Setting::get('app_name', 'Zarządca')),

                                            Forms\Components\Select::make('default_currency')
                                                ->label('Domyślna waluta')
                                                ->options([
                                                    'PLN' => 'PLN (Polski złoty)',
                                                    'EUR' => 'EUR (Euro)',
                                                    'USD' => 'USD (Dolar amerykański)',
                                                ])
                                                ->default(fn () => Setting::get('default_currency', 'PLN')),
                                        ])->columns(2),
                                ]),

                            // Notifications Tab
                            Forms\Components\Tabs\Tab::make('Powiadomienia')
                                ->icon('heroicon-o-bell')
                                ->schema([
                                    Forms\Components\Section::make('Ustawienia powiadomień')
                                        ->schema([
                                            Forms\Components\Toggle::make('email_notifications_enabled')
                                                ->label('Powiadomienia email')
                                                ->default(fn () => Setting::get('email_notifications_enabled', true)),

                                            Forms\Components\Toggle::make('sms_notifications_enabled')
                                                ->label('Powiadomienia SMS')
                                                ->default(fn () => Setting::get('sms_notifications_enabled', false)),
                                        ])->columns(2),
                                ]),

                            // Financial Tab
                            Forms\Components\Tabs\Tab::make('Finanse')
                                ->icon('heroicon-o-banknotes')
                                ->schema([
                                    Forms\Components\Section::make('Ustawienia finansowe')
                                        ->schema([
                                            Forms\Components\TextInput::make('default_payment_deadline_days')
                                                ->label('Domyślny termin płatności (dni)')
                                                ->numeric()
                                                ->minValue(1)
                                                ->maxValue(365)
                                                ->default(fn () => Setting::get('default_payment_deadline_days', 14)),

                                            Forms\Components\TextInput::make('late_payment_fee_percentage')
                                                ->label('Opłata za zwłokę (%)')
                                                ->numeric()
                                                ->step(0.01)
                                                ->minValue(0)
                                                ->maxValue(100)
                                                ->suffix('%')
                                                ->default(fn () => Setting::get('late_payment_fee_percentage', '0.05')),
                                        ])->columns(2),
                                ]),

                            // System Tab
                            Forms\Components\Tabs\Tab::make('System')
                                ->icon('heroicon-o-server-stack')
                                ->schema([
                                    Forms\Components\Section::make('Ustawienia systemowe')
                                        ->schema([
                                            Forms\Components\Toggle::make('backup_enabled')
                                                ->label('Kopie zapasowe włączone')
                                                ->default(fn () => Setting::get('backup_enabled', true)),

                                            Forms\Components\TextInput::make('log_retention_days')
                                                ->label('Przechowywanie logów (dni)')
                                                ->numeric()
                                                ->minValue(1)
                                                ->maxValue(365)
                                                ->default(fn () => Setting::get('log_retention_days', 90)),
                                        ])->columns(2),
                                ]),
                        ])
                ])
                ->action(function (array $data): void {
                    // Save all settings
                    foreach ($data as $key => $value) {
                        $definitions = Setting::getSettingDefinitions();
                        $type = $definitions[$key]['type'] ?? 'string';
                        Setting::set($key, $value, $type);
                    }
                    
                    // Mark app as initialized if not already
                    if (!Setting::get('app_initialized', false)) {
                        Setting::set('app_initialized', true, 'boolean');
                    }

                    Notification::make()
                        ->title('Ustawienia zostały zaktualizowane')
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
                    Setting::set('manager_nip', '');
                    Setting::set('manager_regon', '');

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