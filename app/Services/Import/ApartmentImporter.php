<?php

namespace App\Services\Import;

use App\Models\Apartment;
use App\Models\Community;
use App\Enums\ApartmentType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApartmentImporter extends CsvImporter
{
    protected ?int $communityId = null;

    protected array $requiredColumns = [
        'apartment_number'
    ];

    protected function getColumnMapping(): array
    {
        // Not used in simple approach, but kept for compatibility
        return [
            'apartment_number' => 'apartment_number',
            'numer_lokalu' => 'apartment_number',
            'building_number' => 'building_number',
            'numer_budynku' => 'building_number',
            'area' => 'area',
            'powierzchnia' => 'area',
        ];
    }

    protected function getValidationRules(): array
    {
        return [
            'community_id' => 'required|exists:communities,id',
            'apartment_number' => 'required|string|max:10',
            'building_number' => 'nullable|string|max:10',
            'code' => 'nullable|string|max:20',
            'intercom_code' => 'nullable|string|max:50',
            'land_mortgage_register' => 'nullable|string|max:50',
            'area' => 'nullable|numeric|min:0',
            'basement_area' => 'nullable|numeric|min:0',
            'storage_area' => 'nullable|numeric|min:0',
            'common_area_share' => 'nullable|numeric|min:0|max:100',
            'floor' => 'nullable|integer',
            'elevator_fee_coefficient' => 'nullable|numeric|min:0',
            'has_basement' => 'nullable|boolean',
            'has_storage' => 'nullable|boolean',
            'apartment_type' => 'nullable|string|max:50',
            'usage_description' => 'nullable|string',
            'has_separate_entrance' => 'nullable|boolean',
            'commercial_area' => 'nullable|numeric|min:0',
            'is_commercial' => 'nullable|boolean',
        ];
    }

    protected function getModelClass(): string
    {
        return Apartment::class;
    }

    private function convertToBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string)$value), ['true', '1', 'tak', 'yes', 'prawda', 'x']);
    }

    public function import(string $filePath, array $options = []): array
    {
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

        \Log::info('=== APARTMENT IMPORT STARTED ===', [
            'file_path' => $filePath,
            'community_id' => $this->communityId,
            'options' => $options
        ]);

        // Simple file analysis without problematic encoding detection
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            \Log::info('File analysis:', [
                'file_size' => strlen($content),
                'first_100_chars' => substr($content, 0, 100),
                'line_count' => substr_count($content, "\n") + 1
            ]);
        }

        // Call our custom processing instead of parent
        return $this->processCSVFile($filePath, $options);
    }

    protected function processCSVFile(string $filePath, array $options): array
    {
        $this->resetStats();
        $this->setOptions($options);

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Cannot open file: {$filePath}");
        }

        try {
            return $this->processFileSimple($handle);
        } finally {
            fclose($handle);
        }
    }

    protected function processFileSimple($handle): array
    {
        \Log::info('=== STARTING SIMPLE CSV PROCESSING ===', [
            'delimiter' => $this->delimiter,
            'skip_header' => $this->skipHeader,
            'community_id' => $this->communityId
        ]);

        $rowNumber = 0;
        $headerRow = null;

        while (($row = fgetcsv($handle, 0, $this->delimiter)) !== false) {
            $rowNumber++;
            $this->importStats['total_rows']++;

            \Log::info("Row {$rowNumber}:", [
                'raw_data' => $row,
                'column_count' => count($row),
                'non_empty_count' => count(array_filter($row, function($v) { return trim($v) !== ''; }))
            ]);

            // Handle header row
            if ($rowNumber === 1 && $this->skipHeader) {
                $headerRow = $row;
                \Log::info("Header row stored:", $headerRow);
                continue;
            }

            // Skip empty rows
            $filteredRow = array_filter($row, function($value) { return trim($value) !== ''; });
            if (empty($filteredRow)) {
                $this->importStats['skipped_rows']++;
                \Log::info("Skipping empty row {$rowNumber}");
                continue;
            }

            try {
                // Process the row
                $this->processDataRow($row, $rowNumber);

            } catch (\Exception $e) {
                $this->addError($rowNumber, "Failed to process row: " . $e->getMessage());
                \Log::error("Row {$rowNumber} failed:", [
                    'error' => $e->getMessage(),
                    'row' => $row
                ]);
            }
        }

        \Log::info('CSV Processing completed:', $this->importStats);
        return $this->importStats;
    }

    protected function processDataRow(array $row, int $rowNumber): void
    {
        // 0=building_number, 1=apartment_number, 2=code, 3=intercom_code, 4=land_mortgage_register,
        // 5=area, 6=basement_area, 7=storage_area, 8=common_area_share, 9=floor,
        // 10=elevator_fee_coefficient, 11=has_basement, 12=has_storage, 13=apartment_type,
        // 14=usage_description, 15=has_separate_entrance, 16=commercial_area

        $data = [
            'community_id' => $this->communityId,
        ];

        // Building number (column 0)
        if (isset($row[0]) && trim($row[0]) !== '') {
            $data['building_number'] = trim($row[0]);
        }

        // Apartment number (column 1) - THIS IS THE KEY FIELD
        if (isset($row[1]) && trim($row[1]) !== '') {
            $data['apartment_number'] = trim($row[1]);
        }

        // Code (column 2) - use this if provided, otherwise generate from apartment_number
        if (isset($row[2]) && trim($row[2]) !== '') {
            $data['code'] = trim($row[2]);
        } elseif (!empty($data['apartment_number'])) {
            // Generate code from apartment_number if code column is empty
            $data['code'] = $data['apartment_number'];
        }

        // Intercom code (column 3)
        if (isset($row[3]) && trim($row[3]) !== '') {
            $data['intercom_code'] = trim($row[3]);
        }

        // Land mortgage register (column 4)
        if (isset($row[4]) && trim($row[4]) !== '') {
            $data['land_mortgage_register'] = trim($row[4]);
        }

        // Area (column 5) - handle European decimal format
        if (isset($row[5]) && trim($row[5]) !== '') {
            $area = $this->parseNumericValue($row[5]);
            if ($area !== null && $area > 0) {
                $data['area'] = $area;
            }
        }

        // Basement area (column 6)
        if (isset($row[6]) && trim($row[6]) !== '') {
            $basementArea = $this->parseNumericValue($row[6]);
            if ($basementArea !== null && $basementArea > 0) {
                $data['basement_area'] = $basementArea;
            }
        }

        // Storage area (column 7)
        if (isset($row[7]) && trim($row[7]) !== '') {
            $storageArea = $this->parseNumericValue($row[7]);
            if ($storageArea !== null && $storageArea > 0) {
                $data['storage_area'] = $storageArea;
            }
        }

        // Common area share (column 8) - remove % sign and convert
        if (isset($row[8]) && trim($row[8]) !== '') {
            $share = str_replace('%', '', $row[8]);
            $share = $this->parseNumericValue($share);
            if ($share !== null && $share >= 0 && $share <= 100) {
                $data['common_area_share'] = $share;
            }
        }

        // Floor (column 9)
        if (isset($row[9]) && trim($row[9]) !== '' && is_numeric($row[9])) {
            $data['floor'] = intval($row[9]);
        } else {
            $data['floor'] = 0; // Default floor
        }

        // Elevator fee coefficient (column 10)
        if (isset($row[10]) && trim($row[10]) !== '') {
            $coefficient = $this->parseNumericValue($row[10]);
            if ($coefficient !== null && $coefficient >= 0) {
                $data['elevator_fee_coefficient'] = $coefficient;
            }
        } else {
            $data['elevator_fee_coefficient'] = 1.00; // Default
        }

        // Has basement (column 11)
        if (isset($row[11]) && trim($row[11]) !== '') {
            $data['has_basement'] = $this->convertToBoolean($row[11]);
        } else {
            $data['has_basement'] = false;
        }

        // Has storage (column 12)
        if (isset($row[12]) && trim($row[12]) !== '') {
            $data['has_storage'] = $this->convertToBoolean($row[12]);
        } else {
            $data['has_storage'] = false;
        }

        // Apartment type (column 13)
        if (isset($row[13]) && trim($row[13]) !== '') {
            $data['apartment_type'] = trim($row[13]);
        } else {
            $data['apartment_type'] = 'residential';
        }

        // Usage description (column 14)
        if (isset($row[14]) && trim($row[14]) !== '') {
            $data['usage_description'] = trim($row[14]);
        }

        // Has separate entrance (column 15)
        if (isset($row[15]) && trim($row[15]) !== '') {
            $data['has_separate_entrance'] = $this->convertToBoolean($row[15]);
        } else {
            $data['has_separate_entrance'] = false;
        }

        // Commercial area (column 16)
        if (isset($row[16]) && trim($row[16]) !== '') {
            $commercialArea = $this->parseNumericValue($row[16]);
            if ($commercialArea !== null && $commercialArea > 0) {
                $data['commercial_area'] = $commercialArea;
            }
        }

        // Set is_commercial based on apartment_type
        $data['is_commercial'] = in_array($data['apartment_type'] ?? 'residential', ['commercial', 'mixed']);

        \Log::info("Row {$rowNumber} processed data:", $data);

        // Validate required fields
        if (empty($data['apartment_number'])) {
            $this->addError($rowNumber, 'Missing apartment number');
            return;
        }

        if (empty($data['code'])) {
            $this->addError($rowNumber, 'Missing code');
            return;
        }

        // Check for duplicate apartment_number in same community (this shouldn't happen but let's handle it)
        $existingByApartmentNumber = Apartment::where('community_id', $this->communityId)
                                            ->where('apartment_number', $data['apartment_number'])
                                            ->first();

        if ($existingByApartmentNumber) {
            $this->addError($rowNumber, "Apartment number {$data['apartment_number']} already exists in this community");
            return;
        }

        // Check for duplicate code in same community
        $existingByCode = Apartment::where('community_id', $this->communityId)
                                  ->where('code', $data['code'])
                                  ->first();

        if ($existingByCode) {
            $this->addError($rowNumber, "Code {$data['code']} already exists in this community");
            return;
        }

        $validationRules = [
            'community_id' => 'required|exists:communities,id',
            'apartment_number' => 'required|string|max:10',
            'code' => 'required|string|max:20',
            'floor' => 'required|integer',
            'has_basement' => 'boolean',
            'has_storage' => 'boolean',
            'has_separate_entrance' => 'boolean',
            'is_commercial' => 'boolean',
        ];

        $validator = Validator::make($data, $validationRules);

        if ($validator->fails()) {
            $this->addError($rowNumber, 'Validation failed: ' . implode(', ', $validator->errors()->all()));
            return;
        }

        try {
            Apartment::create($data);
            $this->importStats['successful_imports']++;
            $this->importStats['processed_rows']++;

            \Log::info("Row {$rowNumber} imported successfully - Apartment: {$data['apartment_number']}, Code: {$data['code']}");
        } catch (\Exception $e) {
            $this->addError($rowNumber, "Database error: " . $e->getMessage());
        }
    }

    private function parseNumericValue($value): ?float
    {
        if (empty($value) || !is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // Handle European decimal format (comma as decimal separator)
        $value = str_replace(',', '.', $value);

        // Remove any non-numeric characters except dots and minus
        $value = preg_replace('/[^0-9.-]/', '', $value);

        if (is_numeric($value)) {
            return floatval($value);
        }

        return null;
    }

    protected function addError(int $rowNumber, string $message): void
    {
        $this->importStats['failed_imports']++;
        $this->importStats['errors'][] = __('app.import.row_error', ['row' => $rowNumber, 'message' => $message]);

        \Log::warning('Apartment import error', [
            'row' => $rowNumber,
            'message' => $message,
            'community_id' => $this->communityId
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
