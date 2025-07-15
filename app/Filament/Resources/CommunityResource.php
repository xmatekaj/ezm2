<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityResource\Pages;
use App\Models\Community;
use App\Models\TerritorialUnit;
use App\Models\Street;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Http;

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
            // Basic Information Section - Enhanced with icons and descriptions
            Forms\Components\Section::make(__('app.sections.basic_information'))
                ->description(__('app.communities.basic_info_description'))
                ->icon('heroicon-o-information-circle')
                ->collapsible()
                ->schema([

        Forms\Components\TextInput::make('name')
            ->label(__('app.communities.name'))
            ->required(),

        Forms\Components\TextInput::make('full_name')
            ->label(__('app.communities.full_name'))
            ->required(),


        Forms\Components\TextInput::make('internal_code')
            ->label('Wewnętrzny kod wspólnoty')
            ->maxWidth('xs'),

        ColorPicker::make('color')
            ->label('Kolor')
            ->maxWidth('xs'),

        Forms\Components\Toggle::make('is_active')
            ->label(__('app.communities.is_active'))
            ->default(true),
    ]),

            // Address Section - Enhanced with better UX
            Forms\Components\Section::make(__('app.sections.address'))
                ->description(__('app.communities.address_info_description'))
                ->icon('heroicon-o-map-pin')
                ->collapsible()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('address_state')
                                ->label(__('app.communities.address_state'))
                                ->required()
                                ->options(Community::getVoivodeshipOptions())
                                ->searchable()
                                ->live()
                                ->prefixIcon('heroicon-o-map')
                                ->afterStateUpdated(fn (Forms\Set $set) => $set('address_city', null)),

                            Forms\Components\Select::make('address_city')
                                ->label(__('app.communities.address_city'))
                                ->required()
                                ->searchable()
                                ->prefixIcon('heroicon-o-building-office')
                                ->options(function (Get $get) {
                                    $state = $get('address_state');
                                    if (!$state) {
                                        return [];
                                    }

                                    // Map voivodeship names to codes used in TerritorialUnit
                                    $voivodeshipMap = [
                                        'dolnośląskie' => '02',
                                        'kujawsko-pomorskie' => '04',
                                        'lubelskie' => '06',
                                        'lubuskie' => '08',
                                        'łódzkie' => '10',
                                        'małopolskie' => '12',
                                        'mazowieckie' => '14',
                                        'opolskie' => '16',
                                        'podkarpackie' => '18',
                                        'podlaskie' => '20',
                                        'pomorskie' => '22',
                                        'śląskie' => '24',
                                        'świętokrzyskie' => '26',
                                        'warmińsko-mazurskie' => '28',
                                        'wielkopolskie' => '30',
                                        'zachodniopomorskie' => '32',
                                    ];

                                    $voivodeshipCode = $voivodeshipMap[$state] ?? null;
                                    if (!$voivodeshipCode) {
                                        return [];
                                    }

                                    try {
                                        // Use TerritorialUnit to get all cities for the voivodeship
                                        $cities = TerritorialUnit::getCitiesForVoivodeship($voivodeshipCode);

                                        return $cities->mapWithKeys(function ($city) {
                                            return [$city->nazwa => $city->nazwa];
                                        })->toArray();
                                    } catch (\Exception $e) {
                                        \Illuminate\Support\Facades\Log::error('Failed to fetch cities: ' . $e->getMessage());
                                        return [];
                                    }
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
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('address_street')
                                ->label(__('app.communities.address_street'))
                                ->required()
                                ->searchable()
                                ->prefixIcon('heroicon-o-map-pin')
                                ->options(function (Get $get) {
                                    $city = $get('address_city');
                                    $state = $get('address_state');

                                    if (!$city || !$state) {
                                        return [];
                                    }

                                    // Map voivodeship names to codes
                                    $voivodeshipMap = [
                                        'dolnośląskie' => '02',
                                        'kujawsko-pomorskie' => '04',
                                        'lubelskie' => '06',
                                        'lubuskie' => '08',
                                        'łódzkie' => '10',
                                        'małopolskie' => '12',
                                        'mazowieckie' => '14',
                                        'opolskie' => '16',
                                        'podkarpackie' => '18',
                                        'podlaskie' => '20',
                                        'pomorskie' => '22',
                                        'śląskie' => '24',
                                        'świętokrzyskie' => '26',
                                        'warmińsko-mazurskie' => '28',
                                        'wielkopolskie' => '30',
                                        'zachodniopomorskie' => '32',
                                    ];

                                    $voivodeshipCode = $voivodeshipMap[$state] ?? null;
                                    if (!$voivodeshipCode) {
                                        return [];
                                    }

                                    try {
                                        // Find the territorial unit to get the city code
                                        $territorialUnit = TerritorialUnit::where('woj', $voivodeshipCode)
                                            ->where('nazwa', $city)
                                            ->whereNotNull('pow')
                                            ->whereNotNull('gmi')
                                            ->first();

                                        if (!$territorialUnit) {
                                            return [];
                                        }

                                        // Get streets for this territorial unit using the pow code
                                        $streets = Street::getStreetsForCity($voivodeshipCode, $territorialUnit->pow);

                                        return $streets->mapWithKeys(function ($street) {
                                            return [$street->full_name => $street->full_name];
                                        })->toArray();
                                    } catch (\Exception $e) {
                                        \Illuminate\Support\Facades\Log::error('Failed to fetch streets: ' . $e->getMessage());
                                        return [];
                                    }
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
                                ->label(__('app.communities.address_postal_code'))
                                ->required()
                                ->maxLength(10)
                                ->prefixIcon('heroicon-o-envelope')
                                ->placeholder('00-000')
                                ->mask('99-999'),
                        ]),
                ]),

            // Identifiers Section - Made optional with helpful descriptions
            Forms\Components\Section::make(__('app.sections.identifiers'))
                ->description(__('app.communities.identifiers_description'))
                ->icon('heroicon-o-identification')
                ->collapsible()
                ->collapsed() // Start collapsed since it's optional
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('regon')
                                ->label(__('app.communities.regon'))
                                ->maxLength(20) // No longer required
                                ->prefixIcon('heroicon-o-document-text')
                                ->helperText(__('app.communities.regon_help'))
                                ->placeholder('123456789'),

                            Forms\Components\TextInput::make('tax_id')
                                ->label(__('app.communities.tax_id'))
                                ->maxLength(20) // No longer required
                                ->prefixIcon('heroicon-o-banknotes')
                                ->helperText(__('app.communities.nip_help'))
                                ->placeholder('1234567890'),
                        ]),
                ]),

            // Technical Parameters Section - All optional
            Forms\Components\Section::make(__('app.sections.technical_parameters'))
                ->description(__('app.communities.technical_params_description'))
                ->icon('heroicon-o-wrench-screwdriver')
                ->collapsible()
                ->collapsed() // Start collapsed since it's optional
                ->schema([
                    Forms\Components\Fieldset::make(__('app.sections.surfaces'))
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('total_area')
                                        ->label(__('app.communities.total_area'))
                                        ->numeric()
                                        ->suffix('m²')
                                        ->prefixIcon('heroicon-o-squares-plus')
                                        ->step(0.01)
                                        ->placeholder('np. 1750.25'),

                                    Forms\Components\TextInput::make('apartments_area')
                                        ->label(__('app.communities.apartments_area'))
                                        ->numeric()
                                        ->suffix('m²')
                                        ->prefixIcon('heroicon-o-home')
                                        ->step(0.01)
                                        ->placeholder('np. 1500.75'),
                                ]),
                        ]),

                    Forms\Components\Fieldset::make('Parametry budynku')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('apartment_count')
                                        ->label(__('app.communities.apartment_count'))
                                        ->numeric()
                                        ->prefixIcon('heroicon-o-building-office')
                                        ->minValue(0)
                                        ->placeholder('np. 24'),

                                    Forms\Components\TextInput::make('staircase_count')
                                        ->label(__('app.communities.staircase_count'))
                                        ->numeric()
                                        ->prefixIcon('heroicon-o-building-library')
                                        ->minValue(0)
                                        ->placeholder('np. 2'),

                                    Forms\Components\Toggle::make('has_elevator')
                                        ->label(__('app.communities.has_elevator'))
                                        ->default(false)
                                        ->inline(false),
                                ]),
                        ]),

                    Forms\Components\Fieldset::make('Wodomierze')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('residential_water_meters')
                                        ->label(__('app.communities.residential_water_meters'))
                                        ->numeric()
                                        ->prefixIcon('heroicon-o-beaker')
                                        ->minValue(0)
                                        ->placeholder('np. 24'),

                                    Forms\Components\TextInput::make('main_water_meters')
                                        ->label(__('app.communities.main_water_meters'))
                                        ->numeric()
                                        ->prefixIcon('heroicon-o-wrench')
                                        ->minValue(0)
                                        ->placeholder('np. 2'),
                                ]),
                        ]),
                ]),
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

                Tables\Columns\TextColumn::make('internal_code')
                    ->label('Kod')
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
