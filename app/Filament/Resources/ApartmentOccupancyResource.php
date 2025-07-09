<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApartmentOccupancyResource\Pages;
use App\Models\ApartmentOccupancy;
use App\Models\Apartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApartmentOccupancyResource extends Resource
{
    protected static ?string $model = ApartmentOccupancy::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Zarządzanie';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Zmiana zaludnienia')
                    ->schema([
                        Forms\Components\Select::make('apartment_id')
                            ->label('Mieszkanie')
                            ->options(Apartment::with('community')->get()->mapWithKeys(function ($apartment) {
                                return [$apartment->id => $apartment->community->name . ' - ' . $apartment->full_number];
                            }))
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('number_of_occupants')
                            ->label('Liczba mieszkańców')
                            ->required()
                            ->numeric()
                            ->minValue(0),

                        Forms\Components\DatePicker::make('change_date')
                            ->label('Data zmiany')
                            ->required()
                            ->default(now()),
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

                Tables\Columns\TextColumn::make('number_of_occupants')
                    ->label('Liczba mieszkańców')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('occupancy_change')
                    ->label('Zmiana')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => match(true) {
                        $state === null => 'gray',
                        $state > 0 => 'success',
                        $state < 0 => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn ($state) => $state ? ($state > 0 ? '+' . $state : $state) : 'Brak danych'),

                Tables\Columns\TextColumn::make('change_date')
                    ->label('Data zmiany')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('apartment.community_id')
                    ->label('Wspólnota')
                    ->options(fn () => \App\Models\Community::all()->pluck('name', 'id')),

                Tables\Filters\Filter::make('recent_changes')
                    ->label('Ostatnie 30 dni')
                    ->query(fn (Builder $query): Builder => $query->where('change_date', '>=', now()->subDays(30))),

                Tables\Filters\Filter::make('current_occupancy')
                    ->label('Aktualne zaludnienie')
                    ->query(fn (Builder $query): Builder => $query->where('change_date', '<=', now())
                        ->whereIn('id', function ($subQuery) {
                            $subQuery->select('id')
                                ->from('apartment_occupancies')
                                ->where('change_date', '<=', now())
                                ->orderBy('change_date', 'desc')
                                ->groupBy('apartment_id');
                        })),

                Tables\Filters\Filter::make('increase')
                    ->label('Wzrost zaludnienia')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('
                        number_of_occupants > COALESCE((
                            SELECT number_of_occupants
                            FROM apartment_occupancies ao2
                            WHERE ao2.apartment_id = apartment_occupancies.apartment_id
                            AND ao2.change_date < apartment_occupancies.change_date
                            ORDER BY ao2.change_date DESC
                            LIMIT 1
                        ), 0)
                    ')),

                Tables\Filters\Filter::make('decrease')
                    ->label('Spadek zaludnienia')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('
                        number_of_occupants < COALESCE((
                            SELECT number_of_occupants
                            FROM apartment_occupancies ao2
                            WHERE ao2.apartment_id = apartment_occupancies.apartment_id
                            AND ao2.change_date < apartment_occupancies.change_date
                            ORDER BY ao2.change_date DESC
                            LIMIT 1
                        ), 999)
                    ')),
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
            ->defaultSort('change_date', 'desc');
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
            'index' => Pages\ListApartmentOccupancies::route('/'),
            'create' => Pages\CreateApartmentOccupancy::route('/create'),
            'view' => Pages\ViewApartmentOccupancy::route('/{record}'),
            'edit' => Pages\EditApartmentOccupancy::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationLabel(): string
    {
        return 'Zaludnienie mieszkań';
    }
}
