<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaterMeterResource\Pages;
use App\Models\WaterMeter;
use App\Models\Apartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WaterMeterResource extends Resource
{
    protected static ?string $model = WaterMeter::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Utilities';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Przypisanie')
                    ->schema([
                        Forms\Components\Select::make('apartment_id')
                            ->label('Mieszkanie')
                            ->options(Apartment::with('community')->get()->mapWithKeys(function ($apartment) {
                                return [$apartment->id => $apartment->community->name . ' - ' . $apartment->full_number];
                            }))
                            ->required()
                            ->searchable(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktywny')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Wodomierz')
                    ->schema([
                        Forms\Components\TextInput::make('meter_number')
                            ->label('Numer wodomierza')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('installation_date')
                            ->label('Data instalacji')
                            ->required(),

                        Forms\Components\DatePicker::make('meter_expiry_date')
                            ->label('Data ważności wodomierza')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Nadajnik')
                    ->schema([
                        Forms\Components\TextInput::make('transmitter_number')
                            ->label('Numer nadajnika')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('transmitter_installation_date')
                            ->label('Data instalacji nadajnika'),

                        Forms\Components\DatePicker::make('transmitter_expiry_date')
                            ->label('Data ważności nadajnika'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('apartment.community.name')
                    ->label('Wspólnota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('apartment.full_number')
                    ->label('Mieszkanie')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('meter_number')
                    ->label('Numer wodomierza')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('installation_date')
                    ->label('Data instalacji')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('meter_expiry_date')
                    ->label('Ważność wodomierza')
                    ->date()
                    ->sortable()
                    ->color(fn (WaterMeter $record): string => $record->is_expired ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('transmitter_number')
                    ->label('Numer nadajnika')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('transmitter_expiry_date')
                    ->label('Ważność nadajnika')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(fn (WaterMeter $record): string => $record->is_transmitter_expired ? 'danger' : 'success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktywny')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktywny'),

                Tables\Filters\Filter::make('expired_meter')
                    ->label('Wodomierz wygasł')
                    ->query(fn (Builder $query): Builder => $query->where('meter_expiry_date', '<', now())),

                Tables\Filters\Filter::make('expiring_meter')
                    ->label('Wodomierz wygasa w miesiącu')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('meter_expiry_date', [now(), now()->addMonth()])),

                Tables\Filters\Filter::make('expired_transmitter')
                    ->label('Nadajnik wygasł')
                    ->query(fn (Builder $query): Builder => $query->where('transmitter_expiry_date', '<', now())),
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
            ->defaultSort('apartment.community.name', 'asc');
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
            'index' => Pages\ListWaterMeters::route('/'),
            'create' => Pages\CreateWaterMeter::route('/create'),
            'view' => Pages\ViewWaterMeter::route('/{record}'),
            'edit' => Pages\EditWaterMeter::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationLabel(): string
    {
        return 'Wodomierze';
    }
}
