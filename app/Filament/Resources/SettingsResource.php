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
                // This form won't be used since we're using a custom page
                Forms\Components\TextInput::make('key')
                    ->label('Klucz')
                    ->disabled(),
                    
                Forms\Components\TextInput::make('value')
                    ->label('Wartość'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category_label')
                    ->label('Kategoria')
                    ->searchable(['category'])
                    ->sortable(['category'])
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Zarządca' => 'primary',
                        'Aplikacja' => 'success',
                        'Powiadomienia' => 'warning',
                        'Finanse' => 'info',
                        'System' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('friendly_label')
                    ->label('Nazwa')
                    ->searchable(['label', 'key'])
                    ->sortable(['label']),

                Tables\Columns\TextColumn::make('value')
                    ->label('Wartość')
                    ->limit(50)
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->type === 'boolean') {
                            return $state === 'true' ? 'Tak' : 'Nie';
                        }
                        return $state ?: '-';
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Zaktualizowano')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategoria')
                    ->options(Setting::getCategories()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('key')
                            ->label('Klucz')
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('label')
                            ->label('Nazwa')
                            ->required(),
                            
                        Forms\Components\Select::make('type')
                            ->label('Typ')
                            ->options([
                                'string' => 'Tekst',
                                'boolean' => 'Tak/Nie',
                                'integer' => 'Liczba',
                                'json' => 'JSON',
                            ])
                            ->required(),
                            
                        Forms\Components\Textarea::make('value')
                            ->label('Wartość')
                            ->required(),
                    ]),
            ])
            ->defaultSort('category');
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