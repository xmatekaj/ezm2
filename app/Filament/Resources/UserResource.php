<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'System';
    
    protected static ?string $navigationLabel = 'Użytkownicy';
    
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nazwa użytkownika')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('first_name')
                            ->label('Imię')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_name')
                            ->label('Nazwisko')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Hasło')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\Select::make('user_type')
                            ->label('Typ użytkownika')
                            ->options([
                                'super_admin' => 'Super Administrator',
                                'owner' => 'Właściciel/Mieszkaniec',
                            ])
                            ->required(),

                        Forms\Components\Select::make('person_id')
                            ->label('Powiązana osoba')
                            ->options(Person::all()->pluck('full_name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktywny')
                            ->default(true),

                        Forms\Components\Toggle::make('two_factor_enabled')
                            ->label('2FA włączone')
                            ->default(true),

                        Forms\Components\Select::make('two_factor_method')
                            ->label('Metoda 2FA')
                            ->options([
                                'email' => 'Email',
                                'sms' => 'SMS',
                            ])
                            ->default('email'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nazwa użytkownika')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('first_name')
                    ->label('Imię')
                    ->searchable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nazwisko')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_type')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'owner' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('person.full_name')
                    ->label('Powiązana osoba')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktywny')
                    ->boolean(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Ostatnie logowanie')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_type')
                    ->label('Typ użytkownika')
                    ->options([
                        'super_admin' => 'Super Administrator',
                        'owner' => 'Właściciel/Mieszkaniec',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktywny'),

                Tables\Filters\TernaryFilter::make('two_factor_enabled')
                    ->label('2FA włączone'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}