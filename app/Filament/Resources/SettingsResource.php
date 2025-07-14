<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingsResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingsResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Ustawienia';

    protected static ?string $modelLabel = 'Ustawienie';

    protected static ?string $pluralModelLabel = 'Ustawienia';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Klucz')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Wartość')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string' => 'gray',
                        'boolean' => 'success',
                        'integer' => 'info',
                        'json' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Zaktualizowano')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Typ')
                    ->options([
                        'string' => 'String',
                        'boolean' => 'Boolean',
                        'integer' => 'Integer',
                        'json' => 'JSON',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSettings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}