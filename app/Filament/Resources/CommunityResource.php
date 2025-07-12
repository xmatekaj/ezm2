<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityResource\Pages;
use App\Models\Community;
use App\Services\Import\ImportManager;
use App\Services\Import\CsvTemplateGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class CommunityResource extends Resource
{
    protected static ?string $model = Community::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string
    {
        return __('app.groups.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.navigation.communities');
    }

    public static function getModelLabel(): string
    {
        return __('app.communities.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.communities.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.sections.basic_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('app.communities.name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('full_name')
                            ->label(__('app.communities.full_name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('short_full_name')
                            ->label(__('app.communities.short_full_name'))
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('app.communities.is_active'))
                            ->default(true),

                        Forms\Components\ColorPicker::make('color')
                            ->label(__('app.communities.color'))
                            ->default('#3b82f6'),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.sections.address'))
                    ->schema([
                        Forms\Components\TextInput::make('address_street')
                            ->label(__('app.communities.address_street'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_postal_code')
                            ->label(__('app.communities.address_postal_code'))
                            ->required()
                            ->maxLength(10),

                        Forms\Components\TextInput::make('address_city')
                            ->label(__('app.communities.address_city'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_state')
                            ->label(__('app.communities.address_state'))
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.sections.identifiers'))
                    ->schema([
                        Forms\Components\TextInput::make('regon')
                            ->label(__('app.communities.regon'))
                            ->maxLength(14),

                        Forms\Components\TextInput::make('tax_id')
                            ->label(__('app.communities.tax_id'))
                            ->maxLength(13),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.sections.manager'))
                    ->schema([
                        Forms\Components\TextInput::make('manager_name')
                            ->label(__('app.communities.manager_name'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('manager_address_street')
                            ->label(__('app.communities.manager_address_street'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('manager_address_postal_code')
                            ->label(__('app.communities.manager_address_postal_code'))
                            ->maxLength(10),

                        Forms\Components\TextInput::make('manager_address_city')
                            ->label(__('app.communities.manager_address_city'))
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.sections.technical_parameters'))
                    ->schema([
                        Forms\Components\TextInput::make('common_area_size')
                            ->label(__('app.communities.common_area_size'))
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('apartments_area')
                            ->label(__('app.communities.apartments_area'))
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('apartment_count')
                            ->label(__('app.communities.apartment_count'))
                            ->numeric(),

                        Forms\Components\TextInput::make('staircase_count')
                            ->label(__('app.communities.staircase_count'))
                            ->numeric(),

                        Forms\Components\Toggle::make('has_elevator')
                            ->label(__('app.communities.has_elevator')),

                        Forms\Components\TextInput::make('residential_water_meters')
                            ->label(__('app.communities.residential_water_meters'))
                            ->numeric(),

                        Forms\Components\TextInput::make('main_water_meters')
                            ->label(__('app.communities.main_water_meters'))
                            ->numeric(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('app.communities.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_address')
                    ->label(__('app.communities.full_address'))
                    ->searchable(['address_street', 'address_city']),

                Tables\Columns\TextColumn::make('apartment_count')
                    ->label(__('app.communities.apartment_count'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('manager_name')
                    ->label(__('app.communities.manager_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('app.communities.is_active'))
                    ->boolean(),

                Tables\Columns\ColorColumn::make('color')
                    ->label(__('app.communities.color')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('app.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('app.filters.active')),
            ])
            ->headerActions([
                \App\Filament\Actions\ImportAction::downloadTemplate('communities', __('app.communities.plural')),
                \App\Filament\Actions\ImportAction::make('communities', __('Importuj wspólnoty')),
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
