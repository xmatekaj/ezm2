<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityResource\Pages;
use App\Models\Community;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommunityResource extends Resource
{
    protected static ?string $model = Community::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Zarządzanie';

    protected static ?int $navigationSort = 1;

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

                        Forms\Components\TextInput::make('short_full_name')
                            ->label('Skrócona pełna nazwa')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktywna')
                            ->default(true),

                        Forms\Components\ColorPicker::make('color')
                            ->label('Kolor')
                            ->default('#3b82f6'),
                    ])->columns(2),

                Forms\Components\Section::make('Adres')
                    ->schema([
                        Forms\Components\TextInput::make('address_street')
                            ->label('Ulica')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_postal_code')
                            ->label('Kod pocztowy')
                            ->required()
                            ->maxLength(10),

                        Forms\Components\TextInput::make('address_city')
                            ->label('Miasto')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_state')
                            ->label('Województwo')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Identyfikatory')
                    ->schema([
                        Forms\Components\TextInput::make('regon')
                            ->label('REGON')
                            ->maxLength(14),

                        Forms\Components\TextInput::make('tax_id')
                            ->label('NIP')
                            ->maxLength(13),
                    ])->columns(2),

                Forms\Components\Section::make('Zarządca')
                    ->schema([
                        Forms\Components\TextInput::make('manager_name')
                            ->label('Nazwa zarządcy')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('manager_address_street')
                            ->label('Ulica zarządcy')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('manager_address_postal_code')
                            ->label('Kod pocztowy zarządcy')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('manager_address_city')
                            ->label('Miasto zarządcy')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Parametry techniczne')
                    ->schema([
                        Forms\Components\TextInput::make('common_area_size')
                            ->label('Powierzchnia części wspólnych (m²)')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('apartments_area')
                            ->label('Powierzchnia mieszkań (m²)')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('apartment_count')
                            ->label('Liczba mieszkań')
                            ->numeric(),

                        Forms\Components\TextInput::make('staircase_count')
                            ->label('Liczba klatek')
                            ->numeric(),

                        Forms\Components\Toggle::make('has_elevator')
                            ->label('Winda'),

                        Forms\Components\TextInput::make('residential_water_meters')
                            ->label('Mieszkaniowe wodomierze')
                            ->numeric(),

                        Forms\Components\TextInput::make('main_water_meters')
                            ->label('Główne wodomierze')
                            ->numeric(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_address')
                    ->label('Adres')
                    ->searchable(['address_street', 'address_city']),

                Tables\Columns\TextColumn::make('apartment_count')
                    ->label('Mieszkania')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('manager_name')
                    ->label('Zarządca')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktywna')
                    ->boolean(),

                Tables\Columns\ColorColumn::make('color')
                    ->label('Kolor'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktywna'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'view' => Pages\ViewCommunity::route('/{record}'),
            'edit' => Pages\EditCommunity::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
