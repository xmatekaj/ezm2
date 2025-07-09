<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApartmentResource\Pages;
use App\Models\Apartment;
use App\Models\Community;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApartmentResource extends Resource
{
    protected static ?string $model = Apartment::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Zarządzanie';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\Select::make('community_id')
                            ->label('Wspólnota')
                            ->options(Community::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('building_number')
                            ->label('Numer budynku')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('apartment_number')
                            ->label('Numer mieszkania')
                            ->required()
                            ->maxLength(10),

                        Forms\Components\TextInput::make('floor')
                            ->label('Piętro')
                            ->numeric(),

                        Forms\Components\Toggle::make('is_owned')
                            ->label('Własnościowe')
                            ->default(true),

                        Forms\Components\Toggle::make('is_commercial')
                            ->label('Komercyjne')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Powierzchnie')
                    ->schema([
                        Forms\Components\TextInput::make('area')
                            ->label('Powierzchnia (m²)')
                            ->numeric()
                            ->step(0.01)
                            ->required(),

                        Forms\Components\TextInput::make('heated_area')
                            ->label('Powierzchnia ogrzewana (m²)')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('basement_area')
                            ->label('Powierzchnia piwnicy (m²)')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('storage_area')
                            ->label('Powierzchnia komórki (m²)')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('common_area_share')
                            ->label('Udział w częściach wspólnych (%)')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('elevator_fee_coefficient')
                            ->label('Współczynnik opłaty windowej')
                            ->numeric()
                            ->step(0.01),
                    ])->columns(3),

                Forms\Components\Section::make('Dodatkowe')
                    ->schema([
                        Forms\Components\Toggle::make('has_basement')
                            ->label('Posiada piwnicę'),

                        Forms\Components\Toggle::make('has_storage')
                            ->label('Posiada komórkę'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('community.name')
                    ->label('Wspólnota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_number')
                    ->label('Numer')
                    ->searchable(['building_number', 'apartment_number'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('floor')
                    ->label('Piętro')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('area')
                    ->label('Powierzchnia (m²)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('common_area_share')
                    ->label('Udział (%)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_owned')
                    ->label('Własnościowe')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_commercial')
                    ->label('Komercyjne')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_basement')
                    ->label('Piwnica')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('has_storage')
                    ->label('Komórka')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('community_id')
                    ->label('Wspólnota')
                    ->options(Community::all()->pluck('name', 'id')),

                Tables\Filters\TernaryFilter::make('is_owned')
                    ->label('Własnościowe'),

                Tables\Filters\TernaryFilter::make('is_commercial')
                    ->label('Komercyjne'),

                Tables\Filters\TernaryFilter::make('has_basement')
                    ->label('Z piwnicą'),

                Tables\Filters\TernaryFilter::make('has_storage')
                    ->label('Z komórką'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('community.name', 'asc');
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
            'index' => Pages\ListApartments::route('/'),
            'create' => Pages\CreateApartment::route('/create'),
            'view' => Pages\ViewApartment::route('/{record}'),
            'edit' => Pages\EditApartment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
