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
use Filament\Forms\Get;


class ApartmentResource extends Resource
{
    protected static ?string $model = Apartment::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    //protected static ?string $navigationGroup = 'ZarzÄ…dzanie';

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
                ->description(__('app.apartments.basic_info_description'))
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('community_id')
                                ->label(__('app.common.community'))
                                ->options(Community::all()->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->live()
                                ->prefixIcon('heroicon-o-building-office-2'),

                            Forms\Components\TextInput::make('building_number')
                                ->label(__('app.apartments.building_number'))
                                ->maxLength(10)
                                ->prefixIcon('heroicon-o-building-library'),

                            Forms\Components\TextInput::make('apartment_number')
                                ->label(__('app.apartments.apartment_number'))
                                ->required()
                                ->maxLength(10)
                                ->prefixIcon('heroicon-o-home'),

                            Forms\Components\TextInput::make('code')
                                ->label(__('app.apartments.code'))
                                ->maxLength(20)
                                ->unique(ignoreRecord: true)
                                ->helperText('Unikalny kod w ramach wspólnoty')
                                ->prefixIcon('heroicon-o-tag'),

                            Forms\Components\TextInput::make('intercom_code')
                                ->label(__('app.apartments.intercom_code'))
                                ->maxLength(50)
                                ->prefixIcon('heroicon-o-phone'),

                            Forms\Components\TextInput::make('land_mortgage_register')
                                ->label('Księga Wieczysta')
                                ->maxLength(50)
                                ->prefixIcon('heroicon-o-document-duplicate')
                                ->helperText('Numer księgi wieczystej lokalu')
                                ->placeholder('KA1K/00123456/7'),

                            Forms\Components\TextInput::make('floor')
                                ->label(__('app.apartments.floor'))
                                ->numeric()
                                ->helperText('0 = Parter')
                                ->prefixIcon('heroicon-o-building-office'),

                            Forms\Components\Toggle::make('is_commercial')
                                ->label(__('app.apartments.is_commercial'))
                                ->default(false)
                                ->inline(false),
                        ]),
                ]),

            Forms\Components\Section::make(__('app.sections.surfaces'))
                ->description(__('app.apartments.surfaces_description'))
                ->icon('heroicon-o-square-3-stack-3d')
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('area')
                                ->label(__('app.apartments.area'))
                                ->numeric()
                                ->step(0.01)
                                ->required()
                                ->suffix('m²')
                                ->prefixIcon('heroicon-o-home'),

                            Forms\Components\TextInput::make('common_area_share')
                                ->label(__('app.apartments.common_area_share'))
                                ->numeric()
                                ->step(0.01)
                                ->suffix('%')
                                ->prefixIcon('heroicon-o-chart-pie'),

                            Forms\Components\TextInput::make('elevator_fee_coefficient')
                                ->label(__('app.apartments.elevator_fee_coefficient'))
                                ->numeric()
                                ->step(0.01)
                                ->visible(function (Get $get) {
                                    $communityId = $get('community_id');
                                    return $communityId ? Community::find($communityId)?->has_elevator : true;
                                })
                                ->disabled(function (Get $get) {
                                    $communityId = $get('community_id');
                                    return $communityId ? !Community::find($communityId)?->has_elevator : false;
                                })
                                ->default(1.00)
                                ->prefixIcon('heroicon-o-arrow-trending-up'),
                        ]),
                ]),

            Forms\Components\Section::make(__('app.sections.additional'))
                ->description(__('app.apartments.additional_description'))
                ->icon('heroicon-o-plus-circle')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('has_basement')
                                ->label(__('app.apartments.has_basement'))
                                ->default(false)
                                ->live()
                                ->inline(false),

                            Forms\Components\TextInput::make('basement_area')
                                ->label(__('app.apartments.basement_area_conditional'))
                                ->numeric()
                                ->step(0.01)
                                ->suffix('m²')
                                ->disabled(function (Get $get) {
                                    return !$get('has_basement');
                                })
                                ->prefixIcon('heroicon-o-square-3-stack-3d'),

                            Forms\Components\Toggle::make('has_storage')
                                ->label(__('app.apartments.has_storage'))
                                ->default(false)
                                ->live()
                                ->inline(false),

                            Forms\Components\TextInput::make('storage_area')
                                ->label(__('app.apartments.storage_area_conditional'))
                                ->numeric()
                                ->step(0.01)
                                ->suffix('m²')
                                ->disabled(function (Get $get) {
                                    return !$get('has_storage');
                                })
                                ->prefixIcon('heroicon-o-building-storefront'),
                        ]),

                    Forms\Components\Repeater::make('owners')
                        ->label(__('app.apartments.owners'))
                        ->relationship('people')
                        ->schema([
                            Forms\Components\Select::make('person_id')
                                ->label('Osoba')
                                ->options(\App\Models\Person::all()->pluck('full_name', 'id'))
                                ->searchable()
                                ->required()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('first_name')
                                        ->label('Imię')
                                        ->required(),
                                    Forms\Components\TextInput::make('last_name')
                                        ->label('Nazwisko')
                                        ->required(),
                                    Forms\Components\TextInput::make('email')
                                        ->label('Email')
                                        ->email(),
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Telefon'),
                                ])
                                ->createOptionUsing(function (array $data) {
                                    return \App\Models\Person::create($data)->id;
                                }),

                            Forms\Components\TextInput::make('ownership_share')
                                ->label('Udział własnościowy (%)')
                                ->numeric()
                                ->step(0.01)
                                ->default(100.00)
                                ->suffix('%'),

                            Forms\Components\Toggle::make('is_primary')
                                ->label('Właściciel główny')
                                ->default(false),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->addActionLabel('Dodaj właściciela')
                        ->reorderable(false),
                ]),
        ]);
}

    protected function handleRecordCreation(array $data): Model
    {
        $primaryOwnerId = $data['primary_owner_id'] ?? null;
        unset($data['primary_owner_id']);

        $apartment = static::getModel()::create($data);

        if ($primaryOwnerId) {
            $apartment->people()->attach($primaryOwnerId, [
                'is_primary' => true,
                'ownership_share' => 100.00
            ]);
        }

        return $apartment;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $primaryOwnerId = $data['primary_owner_id'] ?? null;
        unset($data['primary_owner_id']);

        $record->update($data);

        if ($primaryOwnerId) {
            // Remove existing primary owner
            $record->people()->wherePivot('is_primary', true)->detach();

            // Set new primary owner
            $record->people()->attach($primaryOwnerId, [
                'is_primary' => true,
                'ownership_share' => 100.00
            ]);
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['primary_owner_id'] = $this->record->primaryOwner?->id;
        return $data;
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
                ->sortable(query: function (Builder $query, string $direction): Builder {
        return $query->orderByRaw("
            building_number {$direction} NULLS LAST,
            CASE
                WHEN apartment_number ~ '^[0-9]+$' THEN CAST(apartment_number AS INTEGER)
                ELSE 999999
            END {$direction},
            apartment_number {$direction}
        ");
    }),

            Tables\Columns\TextColumn::make('code')
                ->label(__('app.apartments.code'))
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('intercom_code')
                ->label(__('app.apartments.intercom_code'))
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('land_mortgage_register')
                ->label('KW')
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),

            Tables\Columns\TextColumn::make('floor_display')
                ->label(__('app.apartments.floor_display'))
                ->sortable(['floor']),

            Tables\Columns\TextColumn::make('area')
                ->label(__('app.apartments.area'))
                ->numeric(decimalPlaces: 2)
                ->sortable()
                ->suffix(' m²'),

            Tables\Columns\IconColumn::make('is_commercial')
                ->label(__('app.apartments.is_commercial'))
                ->boolean()
                ->toggleable(isToggledHiddenByDefault: true),

//            Tables\Columns\IconColumn::make('has_basement')
                //->label(__('app.apartments.has_basement'))
                //->boolean()
                //->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\IconColumn::make('has_storage')
                ->label(__('app.apartments.has_storage'))
                ->boolean()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('community_id')
                ->label(__('app.common.community'))
                ->options(Community::all()->pluck('name', 'id')),

            Tables\Filters\TernaryFilter::make('is_commercial')
                ->label(__('app.filters.commercial')),

            Tables\Filters\TernaryFilter::make('has_basement')
                ->label(__('app.filters.with_basement')),

            Tables\Filters\TernaryFilter::make('has_storage')
                ->label(__('app.filters.with_storage')),
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
