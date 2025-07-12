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

    //protected static ?string $navigationGroup = 'Zarządzanie';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string
    {
        return __('app.groups.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.navigation.apartments');
    }

    public static function getModelLabel(): string
    {
        return __('app.apartments.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.apartments.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.sections.basic_information'))
                    ->schema([
                        Forms\Components\Select::make('community_id')
                            ->label(__('app.common.community'))
                            ->options(Community::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('building_number')
                            ->label(__('app.apartments.building_number'))
                            ->maxLength(10),

                        Forms\Components\TextInput::make('apartment_number')
                            ->label(__('app.apartments.apartment_number'))
                            ->required()
                            ->maxLength(10),

                        Forms\Components\TextInput::make('floor')
                            ->label(__('app.apartments.floor'))
                            ->numeric(),

                        Forms\Components\Toggle::make('is_owned')
                            ->label(__('app.apartments.is_owned'))
                            ->default(true),

                        Forms\Components\Toggle::make('is_commercial')
                            ->label(__('app.apartments.is_commercial'))
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.sections.surfaces'))
                    ->schema([
                        Forms\Components\TextInput::make('area')
                            ->label(__('app.apartments.area'))
                            ->numeric()
                            ->step(0.01)
                            ->required(),

                        Forms\Components\TextInput::make('heated_area')
                            ->label(__('app.apartments.heated_area'))
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('basement_area')
                            ->label(__('app.apartments.basement_area'))
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('storage_area')
                            ->label(__('app.apartments.storage_area'))
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('common_area_share')
                            ->label(__('app.apartments.common_area_share'))
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('elevator_fee_coefficient')
                            ->label(__('app.apartments.elevator_fee_coefficient'))
                            ->numeric()
                            ->step(0.01),
                    ])->columns(3),

                Forms\Components\Section::make(__('app.sections.additional'))
                    ->schema([
                        Forms\Components\Toggle::make('has_basement')
                            ->label(__('app.apartments.has_basement')),

                        Forms\Components\Toggle::make('has_storage')
                            ->label(__('app.apartments.has_storage')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('community.name')
                    ->label(__('app.common.community'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_number')
                    ->label(__('app.apartments.full_number'))
                    ->searchable(['building_number', 'apartment_number'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('floor')
                    ->label(__('app.apartments.floor'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('area')
                    ->label(__('app.apartments.area'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('common_area_share')
                    ->label(__('app.apartments.common_area_share'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_owned')
                    ->label(__('app.apartments.is_owned'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_commercial')
                    ->label(__('app.apartments.is_commercial'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_basement')
                    ->label(__('app.apartments.has_basement'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('has_storage')
                    ->label(__('app.apartments.has_storage'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('app.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('community_id')
                    ->label(__('app.common.community'))
                    ->options(Community::all()->pluck('name', 'id')),

                Tables\Filters\TernaryFilter::make('is_owned')
                    ->label(__('app.filters.owned')),

                Tables\Filters\TernaryFilter::make('is_commercial')
                    ->label(__('app.filters.commercial')),

                Tables\Filters\TernaryFilter::make('has_basement')
                    ->label(__('app.filters.with_basement')),

                Tables\Filters\TernaryFilter::make('has_storage')
                    ->label(__('app.filters.with_storage')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('app.common.view')),
                Tables\Actions\EditAction::make()
                    ->label(__('app.common.edit')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('app.common.delete')),
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
