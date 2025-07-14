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
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Get;

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

                        Forms\Components\TextInput::make('internal_code')
                            ->label('Wewnętrzny kod wspólnoty')
                            ->maxLength(255),

                        ColorPicker::make('color')
                            ->label('Kolor')
                            ->default('#6366f1'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktywna')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Adres')
                    ->schema([
                        Forms\Components\Select::make('address_state')
                            ->label('Województwo')
                            ->required()
                            ->options(Community::getVoivodeshipOptions())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('address_city', null)),

                        Forms\Components\Select::make('address_city')
                            ->label('Miasto')
                            ->required()
                            ->searchable()
                            ->options(function (Get $get) {
                                $state = $get('address_state');
                                if (!$state) {
                                    return [];
                                }

                                $cities = Community::where('address_state', $state)
                                    ->distinct()
                                    ->whereNotNull('address_city')
                                    ->where('address_city', '!=', '')
                                    ->pluck('address_city')
                                    ->unique()
                                    ->sort()
                                    ->mapWithKeys(fn ($city) => [$city => $city]);

                                return $cities->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('address_street', null))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('city_name')
                                    ->label('Nazwa miasta')
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data, Get $get) {
                                return $data['city_name'];
                            }),

                        Forms\Components\Select::make('address_street')
                            ->label('Ulica')
                            ->required()
                            ->searchable()
                            ->options(function (Get $get) {
                                $city = $get('address_city');
                                $state = $get('address_state');

                                if (!$city || !$state) {
                                    return [];
                                }

                                $streets = Community::where('address_city', $city)
                                    ->where('address_state', $state)
                                    ->distinct()
                                    ->whereNotNull('address_street')
                                    ->where('address_street', '!=', '')
                                    ->pluck('address_street')
                                    ->unique()
                                    ->sort()
                                    ->mapWithKeys(fn ($street) => [$street => $street]);

                                return $streets->toArray();
                            })
                            ->createOptionForm([
                                Forms\Components\TextInput::make('street_name')
                                    ->label('Nazwa ulicy')
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return $data['street_name'];
                            }),

                        Forms\Components\TextInput::make('address_postal_code')
                            ->label('Kod pocztowy')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('00-000'),
                    ])->columns(2),

                Forms\Components\Section::make('Identyfikatory')
                    ->schema([
                        Forms\Components\TextInput::make('regon')
                            ->label('REGON')
                            ->maxLength(9),

                        Forms\Components\TextInput::make('tax_id')
                            ->label('NIP')
                            ->maxLength(10),
                    ])->columns(2),

                Forms\Components\Section::make('Parametry techniczne')
                    ->schema([
                        Forms\Components\TextInput::make('common_area_size')
                            ->label('Powierzchnia części wspólnych (m²)')
                            ->numeric(),

                        Forms\Components\TextInput::make('apartments_area')
                            ->label('Powierzchnia lokali (m²)')
                            ->numeric(),

                        Forms\Components\TextInput::make('apartment_count')
                            ->label('Liczba lokali')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('staircase_count')
                            ->label('Liczba klatek')
                            ->numeric()
                            ->default(1),

                        Forms\Components\Toggle::make('has_elevator')
                            ->label('Winda')
                            ->default(false),

                        Forms\Components\TextInput::make('residential_water_meters')
                            ->label('Wodomierze mieszkaniowe')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('main_water_meters')
                            ->label('Wodomierze główne')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
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

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Pełna nazwa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('internal_code')
                    ->label('Wewnętrzny kod wspólnoty')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('address_city')
                    ->label('Miasto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('address_state')
                    ->label('Województwo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('apartment_count')
                    ->label('Liczba lokali')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\BooleanColumn::make('has_elevator')
                    ->label('Winda')
                    ->sortable(),

                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Aktywna')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Zaktualizowano')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('address_state')
                    ->label('Województwo')
                    ->options(Community::getVoivodeshipOptions()),

                Tables\Filters\SelectFilter::make('address_city')
                    ->label('Miasto')
                    ->options(fn () => Community::distinct()->pluck('address_city', 'address_city')->toArray()),

                Tables\Filters\TernaryFilter::make('has_elevator')
                    ->label('Winda'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktywna'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

    public static function getNavigationLabel(): string
    {
        return __('app.communities.plural');
    }

    public static function getModelLabel(): string
    {
        return __('app.communities.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.communities.plural');
    }
}
