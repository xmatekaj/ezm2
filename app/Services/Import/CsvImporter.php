<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\Validator;
use Exception;

abstract class CsvImporter
{
    protected $batchSize = 500;
    protected $delimiter = ',';
    protected $skipHeader = true;
    
    protected $importStats = [
        'total_rows' => 0,
        'processed_rows' => 0,
        'successful_imports' => 0,
        'failed_imports' => 0,
        'skipped_rows' => 0,
        'errors' => []
    ];

    abstract protected function getColumnMapping(): array;
    abstract protected function getValidationRules(): array;
    abstract protected function getModelClass(): string;

    protected function transformData(array $data): array
    {
        return $data;
    }

    public function import(string $filePath, array $options = []): array
    {
        $this->resetStats();
        $this->setOptions($options);

        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Cannot open file: {$filePath}");
        }

        try {
            return $this->processFile($handle);
        } finally {
            fclose($handle);
        }
    }

    protected function processFile($handle): array
    {
        $rowNumber = 0;
        $columnMapping = $this->getColumnMapping();

        while (($row = fgetcsv($handle, 0, $this->delimiter)) !== false) {
            $rowNumber++;
            $this->importStats['total_rows']++;

            // Skip header row
            if ($rowNumber === 1 && $this->skipHeader) {
                continue;
            }

            // Skip completely empty rows
            if (empty(array_filter($row, function($value) { return trim($value) !== ''; }))) {
                $this->importStats['skipped_rows']++;
                continue;
            }

            try {
                $mappedData = $this->mapRowData($row, $columnMapping);
                
                if (empty($mappedData)) {
                    $this->importStats['skipped_rows']++;
                    continue;
                }

                $this->processRow($mappedData, $rowNumber);
            } catch (Exception $e) {
                $this->addError($rowNumber, "Row processing error: " . $e->getMessage());
            }
        }

        return $this->importStats;
    }

    protected function processRow(array $data, int $rowNumber): void
    {
        $this->importStats['processed_rows']++;

        try {
            // Ensure data is a proper associative array
            if (!is_array($data) || empty($data)) {
                $this->addError($rowNumber, 'Invalid data format');
                return;
            }

            // Get validation rules
            $rules = $this->getValidationRules();
            
            // Ensure rules is a proper array
            if (!is_array($rules)) {
                $this->addError($rowNumber, 'Invalid validation rules');
                return;
            }

            // Create validator with proper arrays
            $validator = Validator::make($data, $rules);
            
            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                $this->addError($rowNumber, 'Validation failed: ' . implode(', ', $errors));
                return;
            }

            // Transform data
            $transformedData = $this->transformData($data);
            
            if (!is_array($transformedData)) {
                $this->addError($rowNumber, 'Data transformation failed');
                return;
            }

            // Create model
            $modelClass = $this->getModelClass();
            
            if (!class_exists($modelClass)) {
                $this->addError($rowNumber, "Model class not found: {$modelClass}");
                return;
            }

            $model = $modelClass::create($transformedData);

            if ($model) {
                $this->importStats['successful_imports']++;
            } else {
                $this->addError($rowNumber, 'Failed to create model');
            }

        } catch (Exception $e) {
            $this->addError($rowNumber, $e->getMessage());
        }
    }

    protected function mapRowData(array $row, array $columnMapping): array
    {
        $mappedData = [];

        // Ensure we have proper arrays
        if (!is_array($row) || !is_array($columnMapping)) {
            return [];
        }

        foreach ($columnMapping as $csvIndex => $modelField) {
            // Skip if not numeric index or invalid field name
            if (!is_numeric($csvIndex) || !is_string($modelField)) {
                continue;
            }

            // Get value from CSV row
            $value = isset($row[$csvIndex]) ? trim((string)$row[$csvIndex]) : '';

            // Only add non-empty values
            if ($value !== '') {
                $mappedData[$modelField] = $value;
            }
        }

        return $mappedData;
    }

    protected function addError(int $rowNumber, string $message): void
    {
        $this->importStats['failed_imports']++;
        $this->importStats['errors'][] = "Row {$rowNumber}: {$message}";
        
        // Log for debugging
        \Log::warning("CSV Import Error", [
            'importer' => static::class,
            'row' => $rowNumber,
            'message' => $message
        ]);
    }

    protected function resetStats(): void
    {
        $this->importStats = [
            'total_rows' => 0,
            'processed_rows' => 0,
            'successful_imports' => 0,
            'failed_imports' => 0,
            'skipped_rows' => 0,
            'errors' => []
        ];
    }

    protected function setOptions(array $options): void
    {
        if (isset($options['batch_size']) && is_numeric($options['batch_size'])) {
            $this->batchSize = (int)$options['batch_size'];
        }
        
        if (isset($options['delimiter']) && is_string($options['delimiter'])) {
            $this->delimiter = $options['delimiter'];
        }
        
        if (isset($options['skip_header'])) {
            $this->skipHeader = (bool)$options['skip_header'];
        }
    }
}