<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Zarządzanie';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dane osobowe')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Imię')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_name')
                            ->label('Nazwisko')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\Select::make('spouse_id')
                            ->label('Małżonek')
                            ->options(Person::all()->pluck('full_name', 'id'))
                            ->searchable(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktywny')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Adres korespondencyjny')
                    ->schema([
                        Forms\Components\TextInput::make('correspondence_address_street')
                            ->label('Ulica')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('correspondence_address_postal_code')
                            ->label('Kod pocztowy')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('correspondence_address_city')
                            ->label('Miasto')
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Dodatkowe informacje')
                    ->schema([
                        Forms\Components\TextInput::make('ownership_share')
                            ->label('Udział własnościowy (%)')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notatki')
                            ->rows(3),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Imię i nazwisko')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('full_address')
                    ->label('Adres')
                    ->searchable(['correspondence_address_street', 'correspondence_address_city'])
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('ownership_share')
                    ->label('Udział (%)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('spouse.full_name')
                    ->label('Małżonek')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktywny')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktywny'),

                Tables\Filters\Filter::make('has_ownership_share')
                    ->label('Ma udział własnościowy')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('ownership_share')),
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
            ->defaultSort('last_name', 'asc');
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
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'view' => Pages\ViewPerson::route('/{record}'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
