<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Models\BankAccount;
use App\Models\Community;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Finanse';

    protected static ?int $navigationSort = 6;

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

                        Forms\Components\TextInput::make('account_number')
                            ->label('Numer konta')
                            ->required()
                            ->maxLength(30)
                            ->placeholder('PL61 1090 1014 0000 0712 1981 2874'),

                        Forms\Components\TextInput::make('swift')
                            ->label('SWIFT/BIC')
                            ->maxLength(11)
                            ->placeholder('WBKPPLPP'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktywne')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Dane banku')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Nazwa banku')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_street')
                            ->label('Ulica')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_postal_code')
                            ->label('Kod pocztowy')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('address_city')
                            ->label('Miasto')
                            ->maxLength(255),
                    ])->columns(2),
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

                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('formatted_account_number')
                    ->label('Numer konta')
                    ->searchable(['account_number'])
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('swift')
                    ->label('SWIFT')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo (PLN)')
                    ->money('PLN')
                    ->sortable()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('full_bank_address')
                    ->label('Adres banku')
                    ->searchable(['address_street', 'address_city'])
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktywne')
                    ->boolean(),

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

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktywne'),

                Tables\Filters\Filter::make('positive_balance')
                    ->label('Saldo dodatnie')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('
                        (SELECT COALESCE(SUM(CASE WHEN is_credit = 1 THEN amount ELSE -amount END), 0)
                         FROM financial_transactions
                         WHERE bank_account_id = bank_accounts.id) > 0
                    ')),

                Tables\Filters\Filter::make('negative_balance')
                    ->label('Saldo ujemne')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('
                        (SELECT COALESCE(SUM(CASE WHEN is_credit = 1 THEN amount ELSE -amount END), 0)
                         FROM financial_transactions
                         WHERE bank_account_id = bank_accounts.id) < 0
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
            'index' => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'view' => Pages\ViewBankAccount::route('/{record}'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationLabel(): string
    {
        return 'Konta bankowe';
    }
}
