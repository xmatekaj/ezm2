<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaterReadingResource\Pages;
use App\Models\WaterReading;
use App\Models\WaterMeter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WaterReadingResource extends Resource
{
    protected static ?string $model = WaterReading::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Utilities';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Odczyt wodomierza')
                    ->schema([
                        Forms\Components\Select::make('water_meter_id')
                            ->label('Wodomierz')
                            ->options(WaterMeter::with(['apartment.community'])->get()->mapWithKeys(function ($meter) {
                                return [$meter->id => $meter->apartment->community->name . ' - ' . $meter->apartment->full_number . ' (' . $meter->meter_number . ')'];
                            }))
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('reading')
                            ->label('Odczyt (m³)')
                            ->required()
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\DateTimePicker::make('reading_date')
                            ->label('Data odczytu')
                            ->required()
                            ->default(now()),
                    ])->columns(2),

                Forms\Components\Section::make('Alarmy')
                    ->schema([
                        Forms\Components\Toggle::make('reverse_flow_alarm')
                            ->label('Alarm zwrotnego przepływu'),

                        Forms\Components\Toggle::make('magnet_alarm')
                            ->label('Alarm magnetyczny'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('waterMeter.apartment.community.name')
                    ->label('Wspólnota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('waterMeter.apartment.full_number')
                    ->label('Mieszkanie')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('waterMeter.meter_number')
                    ->label('Wodomierz')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reading')
                    ->label('Odczyt (m³)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('consumption')
                    ->label('Zużycie (m³)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('reading_date')
                    ->label('Data odczytu')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('reverse_flow_alarm')
                    ->label('Alarm zwrotny')
                    ->boolean()
                    ->color(fn ($state) => $state ? 'danger' : 'gray'),

                Tables\Columns\IconColumn::make('magnet_alarm')
                    ->label('Alarm magnetyczny')
                    ->boolean()
                    ->color(fn ($state) => $state ? 'danger' : 'gray'),

                Tables\Columns\IconColumn::make('has_alarms')
                    ->label('Alarmy')
                    ->boolean()
                    ->color(fn ($state) => $state ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('with_alarms')
                    ->label('Z alarmami')
                    ->query(fn (Builder $query): Builder => $query->where(function ($query) {
                        $query->where('reverse_flow_alarm', true)
                              ->orWhere('magnet_alarm', true);
                    })),

                Tables\Filters\Filter::make('reverse_flow_alarm')
                    ->label('Alarm zwrotnego przepływu')
                    ->query(fn (Builder $query): Builder => $query->where('reverse_flow_alarm', true)),

                Tables\Filters\Filter::make('magnet_alarm')
                    ->label('Alarm magnetyczny')
                    ->query(fn (Builder $query): Builder => $query->where('magnet_alarm', true)),

                Tables\Filters\Filter::make('recent_readings')
                    ->label('Ostatnie 30 dni')
                    ->query(fn (Builder $query): Builder => $query->where('reading_date', '>=', now()->subDays(30))),
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
            ->defaultSort('reading_date', 'desc');
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
            'index' => Pages\ListWaterReadings::route('/'),
            'create' => Pages\CreateWaterReading::route('/create'),
            'view' => Pages\ViewWaterReading::route('/{record}'),
            'edit' => Pages\EditWaterReading::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationLabel(): string
    {
        return 'Odczyty wodomierzy';
    }
}
