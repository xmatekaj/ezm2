<?php
// app/Enums/ApartmentType.php
namespace App\Enums;

enum ApartmentType: string
{
    case RESIDENTIAL = 'residential';
    case COMMERCIAL = 'commercial';
    case MIXED = 'mixed';
    case STORAGE = 'storage';
    case GARAGE = 'garage';

    public function label(): string
    {
        return match($this) {
            self::RESIDENTIAL => __('app.apartments.types.residential'),
            self::COMMERCIAL => __('app.apartments.types.commercial'),
            self::MIXED => __('app.apartments.types.mixed'),
            self::STORAGE => __('app.apartments.types.storage'),
            self::GARAGE => __('app.apartments.types.garage'),
        };
    }

    public function description(): string
    {
        return match($this) {
            self::RESIDENTIAL => __('Lokal mieszkalny'),
            self::COMMERCIAL => __('Lokal użytkowy (biuro, sklep, usługi)'),
            self::MIXED => __('Lokal mieszany (mieszkalno-użytkowy)'),
            self::STORAGE => __('Komórka lokatorska'),
            self::GARAGE => __('Miejsce parkingowe/garaż'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}

// database/migrations/2025_07_12_100000_enhance_apartment_types.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            // Add new apartment_type enum column
            $table->enum('apartment_type', ['residential', 'commercial', 'mixed', 'storage', 'garage'])
                  ->default('residential')
                  ->after('is_commercial');
            
            // Add more specific fields
            $table->text('usage_description')->nullable()->after('apartment_type');
            $table->boolean('has_separate_entrance')->default(false)->after('usage_description');
            $table->decimal('commercial_area', 10, 2)->nullable()->after('has_separate_entrance');
        });

        // Migrate existing data
        DB::statement("
            UPDATE apartments 
            SET apartment_type = CASE 
                WHEN is_commercial = 1 THEN 'commercial'
                ELSE 'residential'
            END
        ");
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn([
                'apartment_type',
                'usage_description', 
                'has_separate_entrance',
                'commercial_area'
            ]);
        });
    }
};

// Updated app/Models/Apartment.php additions
public function getTypeDisplayAttribute(): string
{
    return ApartmentType::from($this->apartment_type)->label();
}

public function isResidential(): bool
{
    return $this->apartment_type === ApartmentType::RESIDENTIAL->value;
}

public function isCommercial(): bool
{
    return in_array($this->apartment_type, [
        ApartmentType::COMMERCIAL->value,
        ApartmentType::MIXED->value
    ]);
}

public function isStorage(): bool
{
    return $this->apartment_type === ApartmentType::STORAGE->value;
}

// Updated translations in lang/pl/app.php for apartments section
'apartments' => [
    'singular' => 'Lokal',
    'plural' => 'Lokale',
    'building_number' => 'Numer budynku',
    'apartment_number' => 'Numer lokalu',
    'apartment_type' => 'Typ lokalu',
    'usage_description' => 'Opis przeznaczenia',
    'has_separate_entrance' => 'Osobne wejście',
    'commercial_area' => 'Powierzchnia użytkowa (m²)',
    'area' => 'Powierzchnia (m²)',
    'basement_area' => 'Powierzchnia piwnicy (m²)',
    'storage_area' => 'Powierzchnia komórki (m²)',
    'heated_area' => 'Powierzchnia ogrzewana (m²)',
    'common_area_share' => 'Udział w częściach wspólnych (%)',
    'floor' => 'Piętro',
    'elevator_fee_coefficient' => 'Współczynnik opłaty windowej',
    'has_basement' => 'Posiada piwnicę',
    'has_storage' => 'Posiada komórkę',
    'is_owned' => 'Własnościowy',
    'is_commercial' => 'Komercyjny', // Keep for backward compatibility
    'full_number' => 'Pełny numer',
    'type_display' => 'Typ',
    'types' => [
        'residential' => 'Mieszkalny',
        'commercial' => 'Użytkowy',
        'mixed' => 'Mieszany',
        'storage' => 'Komórka',
        'garage' => 'Garaż/Parking',
    ],
],

// Updated ApartmentResource form section
Forms\Components\Section::make(__('Podstawowe informacje'))
    ->schema([
        Forms\Components\Select::make('community_id')
            ->label(__('app.common.community'))
            ->options(Community::all()->pluck('name', 'id'))
            ->required()
            ->searchable(),

        Forms\Components\TextInput::make('building_number')
            ->label(__('app.apartments.building_number'))
            ->maxLength(10),

        Forms\Components\TextInput::make('apartment_number')
            ->label(__('app.apartments.apartment_number'))
            ->required()
            ->maxLength(10),

        Forms\Components\Select::make('apartment_type')
            ->label(__('app.apartments.apartment_type'))
            ->options(ApartmentType::options())
            ->default('residential')
            ->required()
            ->live()
            ->afterStateUpdated(function ($state, callable $set) {
                // Auto-set is_commercial based on type for backward compatibility
                $set('is_commercial', in_array($state, ['commercial', 'mixed']));
            }),

        Forms\Components\Textarea::make('usage_description')
            ->label(__('app.apartments.usage_description'))
            ->rows(2)
            ->visible(fn (callable $get) => in_array($get('apartment_type'), ['commercial', 'mixed'])),

        Forms\Components\TextInput::make('floor')
            ->label(__('app.apartments.floor'))
            ->numeric(),

        Forms\Components\Toggle::make('has_separate_entrance')
            ->label(__('app.apartments.has_separate_entrance'))
            ->visible(fn (callable $get) => in_array($get('apartment_type'), ['commercial', 'mixed'])),

        Forms\Components\Toggle::make('is_owned')
            ->label(__('app.apartments.is_owned'))
            ->default(true),

        // Keep is_commercial hidden for backward compatibility
        Forms\Components\Hidden::make('is_commercial'),
    ])->columns(2),

// Sample updated table column for apartment type
Tables\Columns\TextColumn::make('type_display')
    ->label(__('app.apartments.type_display'))
    ->badge()
    ->color(fn (string $state): string => match ($state) {
        'Mieszkalny' => 'success',
        'Użytkowy' => 'info', 
        'Mieszany' => 'warning',
        'Komórka' => 'gray',
        'Garaż/Parking' => 'secondary',
        default => 'gray',
    }),

// Command to migrate apartment types
// php artisan make:command MigrateApartmentTypes

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apartment;
use App\Enums\ApartmentType;

class MigrateApartmentTypes extends Command
{
    protected $signature = 'apartments:migrate-types {--dry-run : Show what would be changed without making changes}';
    protected $description = 'Migrate apartment types from boolean is_commercial to enum apartment_type';

    public function handle()
    {
        $apartments = Apartment::all();
        $changes = [];

        foreach ($apartments as $apartment) {
            $newType = $apartment->is_commercial 
                ? ApartmentType::COMMERCIAL->value 
                : ApartmentType::RESIDENTIAL->value;

            if ($apartment->apartment_type !== $newType) {
                $changes[] = [
                    'id' => $apartment->id,
                    'number' => $apartment->full_number,
                    'current' => $apartment->apartment_type,
                    'new' => $newType
                ];

                if (!$this->option('dry-run')) {
                    $apartment->update(['apartment_type' => $newType]);
                }
            }
        }

        if (empty($changes)) {
            $this->info('No apartments need type migration.');
            return 0;
        }

        $this->table(
            ['ID', 'Number', 'Current Type', 'New Type'],
            $changes
        );

        if ($this->option('dry-run')) {
            $this->info('This was a dry run. Use --no-dry-run to make actual changes.');
        } else {
            $this->info(count($changes) . ' apartments updated successfully.');
        }

        return 0;
    }
}