<?php

// Import Job Model
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'filename',
        'file_path',
        'status',
        'options',
        'stats',
        'started_at',
        'completed_at',
        'user_id'
    ];

    protected $casts = [
        'options' => 'array',
        'stats' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            default => 'gray'
        };
    }

    public function getSuccessRateAttribute(): ?float
    {
        if (!$this->stats || !isset($this->stats['processed_rows'])) {
            return null;
        }

        $processed = $this->stats['processed_rows'];
        $successful = $this->stats['successful_imports'] ?? 0;

        return $processed > 0 ? ($successful / $processed) * 100 : 0;
    }
}

// Import Job Migration
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // communities, apartments, people, etc.
            $table->string('filename');
            $table->string('file_path');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('options')->nullable(); // Import options (delimiter, encoding, etc.)
            $table->json('stats')->nullable(); // Import statistics
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_jobs');
    }
};

// Import Controller
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Import\ImportManager;
use App\Models\ImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    protected ImportManager $importManager;

    public function __construct(ImportManager $importManager)
    {
        $this->importManager = $importManager;
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:' . implode(',', $this->importManager->getAvailableImporters()),
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
            'options' => 'nullable|array',
        ]);

        // Store uploaded file
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $path = $file->store('imports', 'local');

        // Create import job
        $importJob = ImportJob::create([
            'type' => $request->type,
            'filename' => $filename,
            'file_path' => $path,
            'options' => $request->options ?? [],
            'user_id' => auth()->id(),
        ]);

        // Process import immediately (for small files) or queue it
        $this->processImport($importJob);

        return response()->json([
            'success' => true,
            'import_job_id' => $importJob->id,
            'message' => 'Import started successfully'
        ]);
    }

    public function show(ImportJob $importJob)
    {
        return response()->json($importJob->load('user'));
    }

    protected function processImport(ImportJob $importJob)
    {
        try {
            $importJob->update([
                'status' => 'processing',
                'started_at' => now()
            ]);

            $filePath = Storage::disk('local')->path($importJob->file_path);
            $stats = $this->importManager->import(
                $importJob->type,
                $filePath,
                $importJob->options
            );

            $importJob->update([
                'status' => 'completed',
                'stats' => $stats,
                'completed_at' => now()
            ]);

        } catch (\Exception $e) {
            $importJob->update([
                'status' => 'failed',
                'stats' => ['error' => $e->getMessage()],
                'completed_at' => now()
            ]);
        }
    }
}

// Filament Import Resource
namespace App\Filament\Resources;

use App\Filament\Resources\ImportJobResource\Pages;
use App\Models\ImportJob;
use App\Services\Import\ImportManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ImportJobResource extends Resource
{
    protected static ?string $model = ImportJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Import Configuration')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Import Type')
                            ->options([
                                'communities' => 'Communities',
                                'apartments' => 'Apartments',
                                'people' => 'People',
                                'water_meters' => 'Water Meters',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('options', [])),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('CSV File')
                            ->required()
                            ->acceptedFileTypes(['text/csv', 'application/csv'])
                            ->maxSize(10240) // 10MB
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private'),
                    ])->columns(2),

                Forms\Components\Section::make('Import Options')
                    ->schema([
                        Forms\Components\TextInput::make('options.delimiter')
                            ->label('CSV Delimiter')
                            ->default(',')
                            ->maxLength(1),

                        Forms\Components\Select::make('options.encoding')
                            ->label('File Encoding')
                            ->options([
                                'UTF-8' => 'UTF-8',
                                'ISO-8859-1' => 'ISO-8859-1 (Latin-1)',
                                'ISO-8859-2' => 'ISO-8859-2 (Latin-2)',
                                'Windows-1250' => 'Windows-1250',
                            ])
                            ->default('UTF-8'),

                        Forms\Components\Toggle::make('options.skip_header')
                            ->label('Skip Header Row')
                            ->default(true),

                        Forms\Components\TextInput::make('options.batch_size')
                            ->label('Batch Size')
                            ->numeric()
                            ->default(500)
                            ->min(10)
                            ->max(2000),

                        // Conditional field for apartments
                        Forms\Components\Select::make('options.community_id')
                            ->label('Target Community')
                            ->options(\App\Models\Community::pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'apartments')
                            ->helperText('Select the community for apartment imports'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Import Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'communities' => 'info',
                        'apartments' => 'success',
                        'people' => 'warning',
                        'water_meters' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('filename')
                    ->label('File')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (ImportJob $record): string => $record->status_color),

                Tables\Columns\TextColumn::make('stats.processed_rows')
                    ->label('Processed')
                    ->numeric()
                    ->default('—'),

                Tables\Columns\TextColumn::make('stats.successful_imports')
                    ->label('Successful')
                    ->numeric()
                    ->default('—')
                    ->color('success'),

                Tables\Columns\TextColumn::make('stats.failed_imports')
                    ->label('Failed')
                    ->numeric()
                    ->default('—')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('success_rate')
                    ->label('Success Rate')
                    ->formatStateUsing(fn (?float $state): string => $state ? number_format($state, 1) . '%' : '—')
                    ->color(fn (?float $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 95 => 'success',
                        $state >= 80 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Imported By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Import Type')
                    ->options([
                        'communities' => 'Communities',
                        'apartments' => 'Apartments',
                        'people' => 'People',
                        'water_meters' => 'Water Meters',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\Filter::make('recent')
                    ->label('Recent (24h)')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDay())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('download_errors')
                    ->label('Download Errors')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (ImportJob $record): bool => 
                        $record->status === 'failed' || 
                        ($record->stats['failed_imports'] ?? 0) > 0
                    )
                    ->action(function (ImportJob $record) {
                        $errors = $record->stats['errors'] ?? [];
                        $content = "Import Errors Report\n";
                        $content .= "File: {$record->filename}\n";
                        $content .= "Date: " . $record->created_at->format('Y-m-d H:i:s') . "\n\n";
                        
                        foreach ($errors as $error) {
                            $content .= $error . "\n";
                        }
                        
                        return response()->streamDownload(
                            fn () => print($content),
                            "import-errors-{$record->id}.txt",
                            ['Content-Type' => 'text/plain']
                        );
                    }),

                Tables\Actions\Action::make('retry')
                    ->label('Retry Import')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (ImportJob $record): bool => $record->status === 'failed')
                    ->requiresConfirmation()
                    ->action(function (ImportJob $record) {
                        $record->update([
                            'status' => 'pending',
                            'stats' => null,
                            'started_at' => null,
                            'completed_at' => null,
                        ]);
                        
                        // You could dispatch a job here for background processing
                        // ProcessImportJob::dispatch($record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            // Delete associated files
                            foreach ($records as $record) {
                                \Storage::disk('local')->delete($record->file_path);
                            }
                            $records->each->delete();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImportJobs::route('/'),
            'create' => Pages\CreateImportJob::route('/create'),
            'view' => Pages\ViewImportJob::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'processing')->count() ?: null;
    }

    public static function getNavigationLabel(): string
    {
        return 'Data Import';
    }
}

// Import Job Pages
namespace App\Filament\Resources\ImportJobResource\Pages;

use App\Filament\Resources\ImportJobResource;
use App\Services\Import\ImportManager;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateImportJob extends CreateRecord
{
    protected static string $resource = ImportJobResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'pending';
        
        // Extract filename from file path
        if (isset($data['file_path'])) {
            $data['filename'] = basename($data['file_path']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Process the import after creation
        $this->processImport();
    }

    protected function processImport(): void
    {
        $record = $this->getRecord();
        
        try {
            $record->update([
                'status' => 'processing',
                'started_at' => now()
            ]);

            $importManager = app(ImportManager::class);
            $filePath = Storage::disk('local')->path($record->file_path);
            
            $stats = $importManager->import(
                $record->type,
                $filePath,
                $record->options ?? []
            );

            $record->update([
                'status' => 'completed',
                'stats' => $stats,
                'completed_at' => now()
            ]);

            $this->getPage()->setCreatedNotification();

        } catch (\Exception $e) {
            $record->update([
                'status' => 'failed',
                'stats' => ['error' => $e->getMessage()],
                'completed_at' => now()
            ]);

            \Filament\Notifications\Notification::make()
                ->title('Import Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $record = $this->getRecord();
        
        if ($record->status === 'completed') {
            $stats = $record->stats;
            return \Filament\Notifications\Notification::make()
                ->title('Import Completed Successfully')
                ->body("Processed {$stats['processed_rows']} rows. {$stats['successful_imports']} successful, {$stats['failed_imports']} failed.")
                ->success();
        }

        return null;
    }
}

namespace App\Filament\Resources\ImportJobResource\Pages;

use App\Filament\Resources\ImportJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImportJobs extends ListRecords
{
    protected static string $resource = ImportJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Import'),
        ];
    }
}

namespace App\Filament\Resources\ImportJobResource\Pages;

use App\Filament\Resources\ImportJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewImportJob extends ViewRecord
{
    protected static string $resource = ImportJobResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Import Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('type')
                            ->label('Import Type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'communities' => 'info',
                                'apartments' => 'success',
                                'people' => 'warning',
                                'water_meters' => 'primary',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('filename')
                            ->label('Filename'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($record): string => $record->status_color),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Imported By'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Completed')
                            ->dateTime()
                            ->placeholder('Not completed'),
                    ])->columns(2),

                Infolists\Components\Section::make('Import Options')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('options')
                            ->label('Configuration')
                            ->keyLabel('Option')
                            ->valueLabel('Value'),
                    ])
                    ->visible(fn ($record) => !empty($record->options)),

                Infolists\Components\Section::make('Import Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('stats.total_rows')
                            ->label('Total Rows')
                            ->numeric(),

                        Infolists\Components\TextEntry::make('stats.processed_rows')
                            ->label('Processed Rows')
                            ->numeric(),

                        Infolists\Components\TextEntry::make('stats.successful_imports')
                            ->label('Successful Imports')
                            ->numeric()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('stats.failed_imports')
                            ->label('Failed Imports')
                            ->numeric()
                            ->color('danger'),

                        Infolists\Components\TextEntry::make('stats.skipped_rows')
                            ->label('Skipped Rows')
                            ->numeric()
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('success_rate')
                            ->label('Success Rate')
                            ->formatStateUsing(fn (?float $state): string => $state ? number_format($state, 1) . '%' : '—')
                            ->color(fn (?float $state): string => match (true) {
                                $state === null => 'gray',
                                $state >= 95 => 'success',
                                $state >= 80 => 'warning',
                                default => 'danger',
                            }),
                    ])->columns(3)
                    ->visible(fn ($record) => !empty($record->stats)),

                Infolists\Components\Section::make('Error Details')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('stats.errors')
                            ->label('Errors')
                            ->schema([
                                Infolists\Components\TextEntry::make('.')
                                    ->label('')
                                    ->color('danger'),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->stats['errors'])),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_errors')
                ->label('Download Errors')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->visible(fn ($record): bool => 
                    $record->status === 'failed' || 
                    ($record->stats['failed_imports'] ?? 0) > 0
                )
                ->action(function ($record) {
                    $errors = $record->stats['errors'] ?? [];
                    $content = "Import Errors Report\n";
                    $content .= "File: {$record->filename}\n";
                    $content .= "Date: " . $record->created_at->format('Y-m-d H:i:s') . "\n\n";
                    
                    foreach ($errors as $error) {
                        $content .= $error . "\n";
                    }
                    
                    return response()->streamDownload(
                        fn () => print($content),
                        "import-errors-{$record->id}.txt",
                        ['Content-Type' => 'text/plain']
                    );
                }),

            Actions\Action::make('retry')
                ->label('Retry Import')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn ($record): bool => $record->status === 'failed')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'pending',
                        'stats' => null,
                        'started_at' => null,
                        'completed_at' => null,
                    ]);
                    
                    return redirect()->route('filament.admin.resources.import-jobs.view', $record);
                }),
        ];
    }
}

// Console Command for CLI imports
namespace App\Console\Commands;

use App\Services\Import\ImportManager;
use App\Models\ImportJob;
use Illuminate\Console\Command;

class ImportDataCommand extends Command
{
    protected $signature = 'import:data 
                            {type : Type of import (communities, apartments, people, water_meters)}
                            {file : Path to CSV file}
                            {--community-id= : Community ID for apartment imports}
                            {--delimiter=, : CSV delimiter}
                            {--encoding=UTF-8 : File encoding}
                            {--batch-size=500 : Batch size for processing}
                            {--skip-header : Skip header row}
                            {--save-job : Save import job to database}';

    protected $description = 'Import data from CSV files';

    protected ImportManager $importManager;

    public function __construct(ImportManager $importManager)
    {
        parent::__construct();
        $this->importManager = $importManager;
    }

    public function handle(): int
    {
        $type = $this->argument('type');
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        if (!in_array($type, $this->importManager->getAvailableImporters())) {
            $this->error("Invalid import type: {$type}");
            $this->info('Available types: ' . implode(', ', $this->importManager->getAvailableImporters()));
            return 1;
        }

        $options = [
            'delimiter' => $this->option('delimiter'),
            'encoding' => $this->option('encoding'),
            'batch_size' => (int) $this->option('batch-size'),
            'skip_header' => $this->option('skip-header'),
        ];

        if ($this->option('community-id')) {
            $options['community_id'] = (int) $this->option('community-id');
        }

        $importJob = null;
        if ($this->option('save-job')) {
            $importJob = ImportJob::create([
                'type' => $type,
                'filename' => basename($file),
                'file_path' => $file,
                'options' => $options,
                'status' => 'processing',
                'started_at' => now(),
                'user_id' => 1, // System user
            ]);
        }

        $this->info("Starting import of {$type} from {$file}");

        try {
            $stats = $this->importManager->import($type, $file, $options);

            if ($importJob) {
                $importJob->update([
                    'status' => 'completed',
                    'stats' => $stats,
                    'completed_at' => now()
                ]);
            }

            $this->info('Import completed successfully!');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Rows', $stats['total_rows']],
                    ['Processed Rows', $stats['processed_rows']],
                    ['Successful Imports', $stats['successful_imports']],
                    ['Failed Imports', $stats['failed_imports']],
                    ['Skipped Rows', $stats['skipped_rows']],
                ]
            );

            if (!empty($stats['errors'])) {
                $this->warn('Errors occurred during import:');
                foreach (array_slice($stats['errors'], 0, 10) as $error) {
                    $this->line("  • {$error}");
                }
                if (count($stats['errors']) > 10) {
                    $this->line("  ... and " . (count($stats['errors']) - 10) . " more errors");
                }
            }

            return 0;

        } catch (\Exception $e) {
            if ($importJob) {
                $importJob->update([
                    'status' => 'failed',
                    'stats' => ['error' => $e->getMessage()],
                    'completed_at' => now()
                ]);
            }

            $this->error("Import failed: {$e->getMessage()}");
            return 1;
        }
    }
}