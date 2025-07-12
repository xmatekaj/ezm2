<?php

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
