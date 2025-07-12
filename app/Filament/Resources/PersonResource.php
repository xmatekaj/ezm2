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

    protected static ?int $navigationSort = 3;

    // Hide the resource name from breadcrumbs
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $breadcrumb = null;

    public static function getNavigationGroup(): string
    {
        return __('app.groups.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.navigation.people');
    }

    public static function getModelLabel(): string
    {
        return __('app.people.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.people.plural');
    }

    // Hide breadcrumb by overriding the title
    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.sections.basic_information'))
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('app.people.first_name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_name')
                            ->label(__('app.people.last_name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label(__('app.people.email'))
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label(__('app.people.phone'))
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\Select::make('spouse_id')
                            ->label(__('app.people.spouse'))
                            ->options(Person::all()->pluck('full_name', 'id'))
                            ->searchable(),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('app.people.is_active'))
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make(__('app.sections.address'))
                    ->schema([
                        Forms\Components\TextInput::make('correspondence_address_street')
                            ->label(__('app.people.correspondence_address_street'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('correspondence_address_postal_code')
                            ->label(__('app.people.correspondence_address_postal_code'))
                            ->maxLength(10),

                        Forms\Components\TextInput::make('correspondence_address_city')
                            ->label(__('app.people.correspondence_address_city'))
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make(__('app.sections.additional'))
                    ->schema([
                        Forms\Components\TextInput::make('ownership_share')
                            ->label(__('app.people.ownership_share'))
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\Textarea::make('notes')
                            ->label(__('app.people.notes'))
                            ->rows(3),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('app.people.full_name'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('app.people.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('app.people.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('full_address')
                    ->label(__('app.people.full_address'))
                    ->searchable(['correspondence_address_street', 'correspondence_address_city'])
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('ownership_share')
                    ->label(__('app.people.ownership_share'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('spouse.full_name')
                    ->label(__('app.people.spouse'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('app.people.is_active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('app.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([

            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('app.filters.active')),

                Tables\Filters\Filter::make('has_ownership_share')
                    ->label(__('Ma udział własnościowy'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('ownership_share')),
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
