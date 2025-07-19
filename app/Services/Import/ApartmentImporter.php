<?php

namespace App\Services\Import;

use App\Models\Apartment;
use App\Models\Community;
use Illuminate\Support\Facades\Validator;

class ApartmentImporter extends CsvImporter
{
    protected int $communityId;

    public function setCommunityId(int $communityId): void
    {
        $this->communityId = $communityId;
    }

    /**
     * Simple column mapping that works with existing architecture
     */
    protected function getColumnMapping(): array
    {
        return [
            0 => 'building_number',
            1 => 'apartment_number',
            2 => 'code',
            3 => 'intercom_code',
            4 => 'land_mortgage_register',
            5 => 'area',
            6 => 'basement_area',
            7 => 'storage_area',
            8 => 'common_area_share',
            9 => 'floor',
            10 => 'elevator_fee_coefficient',
            11 => 'has_basement',
            12 => 'has_storage',
            13 => 'apartment_type',
            14 => 'usage_description',
            15 => 'has_separate_entrance',
            16 => 'commercial_area',
        ];
    }

    /**
     * Override processRow to add debugging and ensure proper arrays
     */
    protected function processRow(array $data, int $rowNumber): void
    {
        $this->importStats['processed_rows']++;

        try {
            // Ensure data is a proper associative array
            if (!is_array($data) || empty($data)) {
                $this->addError($rowNumber, 'Invalid data format');
                return;
            }

            // Debug the data
            \Log::info("Processing row {$rowNumber}:", [
                'data_type' => gettype($data),
                'data_count' => count($data),
                'data_keys' => array_keys($data),
                'data_sample' => array_slice($data, 0, 3)
            ]);

            // Get validation rules and ensure it's an array
            $rules = $this->getValidationRules();

            if (!is_array($rules)) {
                \Log::error("Validation rules is not an array", [
                    'rules_type' => gettype($rules),
                    'rules_value' => $rules
                ]);
                $this->addError($rowNumber, 'Invalid validation rules configuration');
                return;
            }

            \Log::info("Validation rules:", [
                'rules_count' => count($rules),
                'rules_keys' => array_keys($rules)
            ]);

            // Transform data BEFORE validation
            $transformedData = $this->transformData($data);

            if (!is_array($transformedData)) {
                $this->addError($rowNumber, 'Data transformation failed');
                return;
            }

            \Log::info("Transformed data:", [
                'transformed_count' => count($transformedData),
                'transformed_keys' => array_keys($transformedData)
            ]);

            // Create validator with proper arrays
            $validator = Validator::make($transformedData, $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                $this->addError($rowNumber, 'Validation failed: ' . implode(', ', $errors));
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
                \Log::info("Successfully created apartment", [
                    'row' => $rowNumber,
                    'apartment_id' => $model->id,
                    'apartment_number' => $model->apartment_number
                ]);
            } else {
                $this->addError($rowNumber, 'Failed to create model');
            }

        } catch (\Exception $e) {
            \Log::error("Row processing exception", [
                'row' => $rowNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError($rowNumber, "Exception: " . $e->getMessage());
        }
    }

    /**
     * Enhanced data transformation that always returns an array
     */
    protected function transformData(array $data): array
    {
        try {
            // Ensure we have an array
            if (!is_array($data)) {
                \Log::error("transformData received non-array", [
                    'data_type' => gettype($data),
                    'data_value' => $data
                ]);
                return [];
            }

            // Add community ID (always required)
            $data['community_id'] = $this->communityId;

            // Set sensible defaults for missing fields
            $data['owned_by_city'] = false;
            $data['apartment_type'] = $data['apartment_type'] ?? 'residential';

            // Convert boolean fields (only if present)
            $booleanFields = ['has_basement', 'has_storage', 'has_separate_entrance'];
            foreach ($booleanFields as $field) {
                if (isset($data[$field]) && $data[$field] !== '') {
                    $data[$field] = $this->convertToBoolean($data[$field]);
                }
            }

            // Handle apartment type (only if present)
            if (isset($data['apartment_type']) && $data['apartment_type'] !== '') {
                $data['apartment_type'] = $this->normalizeApartmentType($data['apartment_type']);
            }

            // Convert numeric fields (only if present and not empty)
            $numericFields = [
                'area', 'basement_area', 'storage_area', 'common_area_share',
                'elevator_fee_coefficient', 'commercial_area'
            ];
            foreach ($numericFields as $field) {
                if (isset($data[$field]) && $data[$field] !== '') {
                    $data[$field] = $this->convertToDecimal($data[$field]);
                }
            }

            // Convert integer fields (only if present and not empty)
            $integerFields = ['floor'];
            foreach ($integerFields as $field) {
                if (isset($data[$field]) && $data[$field] !== '' && $data[$field] !== 'P') {
                    $data[$field] = (int) $data[$field];
                } else {
                    // Leave as NULL for empty values since column is now nullable
                    unset($data[$field]);
                }
            }

            // Generate code if not provided but apartment_number exists
            if (empty($data['code']) && !empty($data['apartment_number'])) {
                $data['code'] = $data['apartment_number'];
            }

            // Remove any keys with empty string values to avoid validation issues
            // But preserve explicit NULL values for nullable fields
            $data = array_filter($data, function($value, $key) {
                // Keep NULL values for nullable fields like floor
                if ($value === null) {
                    return true;
                }
                // Remove empty strings
                return $value !== '';
            }, ARRAY_FILTER_USE_BOTH);

            return $data;

        } catch (\Exception $e) {
            \Log::error("transformData exception", [
                'error' => $e->getMessage(),
                'data_input' => $data
            ]);
            return [];
        }
    }

    /**
     * Validation rules that always returns a proper array
     */
    protected function getValidationRules(): array
    {
        try {
            $rules = [
                'community_id' => 'required|exists:communities,id',
                'apartment_number' => 'required|string|max:10',
                'building_number' => 'nullable|string|max:10',
                'code' => 'nullable|string|max:10',
                'intercom_code' => 'nullable|string|max:10',
                'land_mortgage_register' => 'nullable|string|max:50',
                'area' => 'nullable|numeric|min:0|max:9999.99',
                'basement_area' => 'nullable|numeric|min:0|max:9999.99',
                'storage_area' => 'nullable|numeric|min:0|max:9999.99',
                'common_area_share' => 'nullable|numeric|min:0',
                'floor' => 'nullable|integer|min:-5|max:50',
                'elevator_fee_coefficient' => 'nullable|numeric|min:0|max:10',
                'has_basement' => 'nullable|boolean',
                'has_storage' => 'nullable|boolean',
                'apartment_type' => 'nullable|string',
                'usage_description' => 'nullable|string|max:500',
                'has_separate_entrance' => 'nullable|boolean',
                'commercial_area' => 'nullable|numeric|min:0',
                'owned_by_city' => 'nullable|boolean',
            ];

            // Ensure it's always an array
            if (!is_array($rules)) {
                \Log::error("Validation rules is not an array!");
                return [];
            }

            return $rules;

        } catch (\Exception $e) {
            \Log::error("getValidationRules exception: " . $e->getMessage());
            return [];
        }
    }

    protected function getModelClass(): string
    {
        return Apartment::class;
    }

    protected function findExistingRecord(array $data): ?Apartment
    {
        return Apartment::where('community_id', $data['community_id'])
            ->where('apartment_number', $data['apartment_number'])
            ->where('building_number', $data['building_number'] ?? null)
            ->first();
    }

    private function convertToBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $trueValues = ['true', '1', 'tak', 'yes', 'prawda', 'x'];
        return in_array(strtolower((string)$value), $trueValues);
    }

    private function convertToDecimal($value): ?float
    {
        if ($value === '' || $value === null) {
            return null;
        }

        // Handle Polish decimal separator
        $value = str_replace(',', '.', (string)$value);
        $value = str_replace('%', '', $value);

        return (float) $value;
    }

    private function normalizeApartmentType(string $type): string
    {
        $type = strtolower(trim($type));

        $mapping = [
            'mieszkaniowy' => 'residential',
            'mieszkalne' => 'residential',
            'komercyjny' => 'commercial',
            'handlowy' => 'commercial',
            'mieszany' => 'mixed',
            'garaż' => 'garage',
            'piwnica' => 'storage',
            'residential' => 'residential',
            'commercial' => 'commercial',
            'mixed' => 'mixed',
            'garage' => 'garage',
            'storage' => 'storage',
        ];

        return $mapping[$type] ?? 'residential';
    }

    public function import(string $filePath, array $options = []): array
    {
        try {
            // Set community ID from options
            if (isset($options['community_id'])) {
                $this->communityId = $options['community_id'];
            }

            if (!$this->communityId) {
                throw new \InvalidArgumentException(__('app.import.apartment.community_required'));
            }

            // Verify community exists
            if (!Community::find($this->communityId)) {
                throw new \InvalidArgumentException(__('app.import.apartment.community_not_found'));
            }

            \Log::info('=== BULLETPROOF APARTMENT IMPORT STARTED ===', [
                'file_path' => $filePath,
                'community_id' => $this->communityId,
                'options' => $options
            ]);

            return parent::import($filePath, $options);

        } catch (\Exception $e) {
            \Log::error('Import failed with exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }
}
