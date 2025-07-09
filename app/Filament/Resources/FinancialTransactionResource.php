<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialTransactionResource\Pages;
use App\Models\FinancialTransaction;
use App\Models\BankAccount;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinancialTransactionResource extends Resource
{
    protected static ?string $model = FinancialTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Finanse';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\Select::make('bank_account_id')
                            ->label('Konto bankowe')
                            ->options(BankAccount::with('community')->get()->mapWithKeys(function ($account) {
                                return [$account->id => $account->community->name . ' - ' . $account->bank_name];
                            }))
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Kwota (PLN)')
                            ->required()
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\Toggle::make('is_credit')
                            ->label('Wpłata')
                            ->default(false)
                            ->helperText('Zaznacz dla wpłat, odznacz dla wydatków'),

                        Forms\Components\DatePicker::make('booking_date')
                            ->label('Data księgowania')
                            ->required()
                            ->default(now()),
                    ])->columns(2),

                Forms\Components\Section::make('Szczegóły transakcji')
                    ->schema([
                        Forms\Components\TextInput::make('transaction_number')
                            ->label('Numer transakcji')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('counterparty_details')
                            ->label('Dane kontrahenta')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('title')
                            ->label('Tytuł przelewu')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('additional_info')
                            ->label('Dodatkowe informacje')
                            ->rows(3),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notatki')
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Powiązania')
                    ->schema([
                        Forms\Components\Select::make('person_id')
                            ->label('Osoba')
                            ->options(Person::all()->pluck('full_name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bankAccount.community.name')
                    ->label('Wspólnota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('bankAccount.bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('booking_date')
                    ->label('Data księgowania')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Wpłata' => 'success',
                        'Wydatek' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('formatted_amount')
                    ->label('Kwota')
                    ->sortable(['amount'])
                    ->fontFamily('mono')
                    ->color(fn (FinancialTransaction $record): string => $record->is_credit ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('counterparty_details')
                    ->label('Kontrahent')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('person.full_name')
                    ->label('Osoba')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('Nr transakcji')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bank_account_id')
                    ->label('Konto bankowe')
                    ->options(BankAccount::with('community')->get()->mapWithKeys(function ($account) {
                        return [$account->id => $account->community->name . ' - ' . $account->bank_name];
                    })),

                Tables\Filters\TernaryFilter::make('is_credit')
                    ->label('Typ transakcji')
                    ->trueLabel('Wpłaty')
                    ->falseLabel('Wydatki'),

                Tables\Filters\Filter::make('amount_range')
                    ->label('Zakres kwot')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->label('Od (PLN)')
                            ->numeric(),
                        Forms\Components\TextInput::make('amount_to')
                            ->label('Do (PLN)')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    }),

                Tables\Filters\Filter::make('date_range')
                    ->label('Zakres dat')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Od'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('recent_transactions')
                    ->label('Ostatnie 30 dni')
                    ->query(fn (Builder $query): Builder => $query->where('booking_date', '>=', now()->subDays(30))),
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
            ->defaultSort('booking_date', 'desc');
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
            'index' => Pages\ListFinancialTransactions::route('/'),
            'create' => Pages\CreateFinancialTransaction::route('/create'),
            'view' => Pages\ViewFinancialTransaction::route('/{record}'),
            'edit' => Pages\EditFinancialTransaction::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationLabel(): string
    {
        return 'Transakcje finansowe';
    }
}
