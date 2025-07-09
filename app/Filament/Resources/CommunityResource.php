<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityResource\Pages;
use App\Models\Community;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CommunityResource extends Resource
{
    protected static ?string $model = Community::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Wspólnoty';
    protected static ?string $modelLabel = 'Wspólnota';
    protected static ?string $pluralModelLabel = 'Wspólnoty';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nazwa')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('full_name')
                            ->label('Pełna nazwa')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('regon')
                            ->label('REGON')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('tax_id')
                            ->label('NIP')
                            ->required()
                            ->maxLength(20),
                    ])->columns(2),

                Forms\Components\Section::make('Adres')
                    ->schema([
                        Forms\Components\TextInput::make('address_street')
                            ->label('Ulica')
                            ->required(),
                        Forms\Components\TextInput::make('address_postal_code')
                            ->label('Kod pocztowy')
                            ->required()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('address_city')
                            ->label('Miejscowość')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('address_state')
                            ->label('Województwo')
                            ->maxLength(50),
                    ])->columns(2),

                Forms\Components\Section::make('Zarządca')
                    ->schema([
                        Forms\Components\TextInput::make('manager_name')
                            ->label('Nazwa zarządcy')
                            ->required(),
                        Forms\Components\TextInput::make('manager_address_street')
                            ->label('Ulica zarządcy')
                            ->required(),
                        Forms\Components\TextInput::make('manager_address_postal_code')
                            ->label('Kod pocztowy zarządcy')
                            ->required()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('manager_address_city')
                            ->label('Miejscowość zarządcy')
                            ->required()
                            ->maxLength(50),
                    ])->columns(2),

                Forms\Components\Section::make('Parametry techniczne')
                    ->schema([
                        Forms\Components\TextInput::make('common_area_size')
                            ->label('Powierzchnia części wspólnej')
                            ->numeric()
                            ->suffix('m²')
                            ->required(),
                        Forms\Components\TextInput::make('apartments_area')
                            ->label('Powierzchnia mieszkań')
                            ->numeric()
                            ->suffix('m²'),
                        Forms\Components\Toggle::make('has_elevator')
                            ->label('Posiada windę'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktywna')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('regon')
                    ->label('REGON')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address_city')
                    ->label('Miejscowość')
                    ->searchable(),
                Tables\Columns\TextColumn::make('apartment_count')
                    ->label('Liczba mieszkań')
                    ->numeric(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktywna')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktywne'),
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
            'index' => Pages\ListCommunities::route('/'),
            'create' => Pages\CreateCommunity::route('/create'),
            'edit' => Pages\EditCommunity::route('/{record}/edit'),
        ];
    }
}
