<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceResource\Pages;
use App\Models\Price;
use App\Models\Community;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PriceResource extends Resource
{
    protected static ?string $model = Price::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Finanse';

    protected static ?int $navigationSort = 7;

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

                        Forms\Components\DatePicker::make('change_date')
                            ->label('Data obowiązywania')
                            ->required()
                            ->default(now()),
                    ])->columns(2),

                Forms\Components\Section::make('Opłaty stałe (PLN)')
                    ->schema([
                        Forms\Components\TextInput::make('garbage_price')
                            ->label('Opłata za odpady')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),

                        Forms\Components\TextInput::make('management_fee')
                            ->label('Opłata za zarządzanie')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),

                        Forms\Components\TextInput::make('renovation_fund')
                            ->label('Fundusz remontowy')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),

                        Forms\Components\TextInput::make('loan_fund')
                            ->label('Fundusz kredytowy')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),

                        Forms\Components\TextInput::make('central_heating_advance')
                            ->label('Zaliczka na c.o.')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Opłaty zmienne (PLN/m³)')
                    ->schema([
                        Forms\Components\TextInput::make('water_sewage_price')
                            ->label('Woda i ścieki')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),
                    ])->columns(1),
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

                Tables\Columns\TextColumn::make('change_date')
                    ->label('Data obowiązywania')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('water_sewage_price')
                    ->label('Woda/ścieki (PLN/m³)')
                    ->money('PLN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('garbage_price')
                    ->label('Odpady (PLN)')
                    ->money('PLN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('management_fee')
                    ->label('Zarządzanie (PLN)')
                    ->money('PLN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('renovation_fund')
                    ->label('Fundusz remontowy (PLN)')
                    ->money('PLN')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('loan_fund')
                    ->label('Fundusz kredytowy (PLN)')
                    ->money('PLN')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('central_heating_advance')
                    ->label('Zaliczka c.o. (PLN)')
                    ->money('PLN')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_monthly_fee')
                    ->label('Suma miesięczna (PLN)')
                    ->money('PLN')
                    ->sortable()
                    ->weight('bold'),

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

                Tables\Filters\Filter::make('current_prices')
                    ->label('Aktualne ceny')
                    ->query(fn (Builder $query): Builder => $query->where('change_date', '<=', now())
                        ->whereIn('id', function ($subQuery) {
                            $subQuery->select('id')
                                ->from('prices')
                                ->where('change_date', '<=', now())
                                ->orderBy('change_date', 'desc')
                                ->groupBy('community_id');
                        })),

                Tables\Filters\Filter::make('future_prices')
                    ->label('Przyszłe ceny')
                    ->query(fn (Builder $query): Builder => $query->where('change_date', '>', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->label('Duplikuj')
                    ->form([
                        Forms\Components\DatePicker::make('change_date')
                            ->label('Nowa data obowiązywania')
                            ->required(),
                    ])
                    ->beforeReplicaSaved(function (array $data, Price $replica): void {
                        $replica->change_date = $data['change_date'];
                    }),
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
            'index' => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'view' => Pages\ViewPrice::route('/{record}'),
            'edit' => Pages\EditPrice::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationLabel(): string
    {
        return 'Cennik';
    }
}
