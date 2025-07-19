<?php

namespace App\Services\Import;

use App\Models\Apartment;
use App\Models\Community;
use App\Enums\ApartmentType;

class ApartmentImporter extends CsvImporter
{
    protected int $communityId;

    public function setCommunityId(int $communityId): void
    {
        $this->communityId = $communityId;
    }

    protected function getColumnMapping(): array
    {
        return [
            'building_number' => 'building_number',
            'apartment_number' => 'apartment_number',
            'code' => 'code',
            'intercom_code' => 'intercom_code',
            'land_mortgage_register' => 'land_mortgage_register',
            'area' => 'area',
            'basement_area' => 'basement_area',
            'storage_area' => 'storage_area',
            'common_area_share' => 'common_area_share',
            'floor' => 'floor',
            'elevator_fee_coefficient' => 'elevator_fee_coefficient',
            'has_basement' => 'has_basement',
            'has_storage' => 'has_storage',
            'apartment_type' => 'apartment_type',
            'usage_description' => 'usage_description',
            'has_separate_entrance' => 'has_separate_entrance',
            'commercial_area' => 'commercial_area',
            // City ownership columns
            'owned_by_city' => 'owned_by_city',
            'city_total_area' => 'city_total_area',
            'city_apartment_count' => 'city_apartment_count',
            'city_common_area_share' => 'city_common_area_share',
        ];
    }

    protected function transformRowData(array $row): array
    {
        $data = parent::transformRowData($row);

        // Add community ID
        $data['community_id'] = $this->communityId;

        // Handle city ownership detection
        $data = $this->processCityOwnership($data);

        // Convert boolean fields
        $booleanFields = ['has_basement', 'has_storage', 'has_separate_entrance', 'owned_by_city'];
        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->convertToBoolean($data[$field]);
            }
        }

        // Handle apartment type
        if (isset($data['apartment_type'])) {
            $data['apartment_type'] = $this->normalizeApartmentType($data['apartment_type']);
        }

        // Convert numeric fields
        $numericFields = [
            'area', 'basement_area', 'storage_area', 'common_area_share',
            'elevator_fee_coefficient', 'commercial_area', 'city_total_area', 'city_common_area_share'
        ];
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $data[$field] = $this->convertToDecimal($data[$field]);
            }
        }

        // Convert integer fields
        $integerFields = ['floor', 'city_apartment_count'];
        foreach ($integerFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $data[$field] = (int) $data[$field];
            }
        }

        // Generate code if not provided
        if (empty($data['code']) && !empty($data['apartment_number'])) {
            $data['code'] = $data['apartment_number'];
        }

        return $data;
    }

    protected function processCityOwnership(array $data): array
    {
        // Auto-detect city ownership based on various indicators
        $cityIndicators = [
            'miasto', 'city', 'gmina', 'urząd', 'rada', 'council',
            'municipal', 'komunalny', 'publiczny'
        ];

        $isCityOwned = false;

        // Check if explicitly marked as city owned
        if (isset($data['owned_by_city'])) {
            $isCityOwned = $this->convertToBoolean($data['owned_by_city']);
        }

        // Auto-detect based on apartment number, code, or other fields
        if (!$isCityOwned) {
            $fieldsToCheck = [
                $data['apartment_number'] ?? '',
                $data['code'] ?? '',
                $data['usage_description'] ?? '',
                $data['land_mortgage_register'] ?? ''
            ];

            foreach ($fieldsToCheck as $field) {
                $field = strtolower(trim($field));
                foreach ($cityIndicators as $indicator) {
                    if (strpos($field, $indicator) !== false) {
                        $isCityOwned = true;
                        break 2;
                    }
                }
            }
        }

        $data['owned_by_city'] = $isCityOwned;

        // Set default values for city apartments
        if ($isCityOwned) {
            // If no individual area but has city_total_area, that's fine
            // If no city_apartment_count specified, default to 1
            if (!isset($data['city_apartment_count']) || empty($data['city_apartment_count'])) {
                $data['city_apartment_count'] = 1;
            }

            // Use city_common_area_share if available, otherwise use regular common_area_share
            if (isset($data['city_common_area_share']) && $data['city_common_area_share']) {
                // Keep the city_common_area_share as is
            } elseif (isset($data['common_area_share']) && $data['common_area_share']) {
                // Move common_area_share to city_common_area_share for city apartments
                $data['city_common_area_share'] = $data['common_area_share'];
            }
        }

        return $data;
    }

    protected function validateRowData(array $data): void
    {
        parent::validateRowData($data);

        // Additional validation for city apartments
        if ($data['owned_by_city'] ?? false) {
            // City apartments must have either individual area or city_total_area
            if (empty($data['area']) && empty($data['city_total_area'])) {
                throw new \InvalidArgumentException(
                    'City apartments must have either individual area or city_total_area'
                );
            }

            // City apartments should have common area share (either regular or city-specific)
            if (empty($data['common_area_share']) && empty($data['city_common_area_share'])) {
                throw new \InvalidArgumentException(
                    'City apartments must have common area share'
                );
            }
        }
    }

    protected function getValidationRules(): array
    {
        return [
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
            'is_commercial' => 'nullable|boolean',
            // City ownership validation
            'owned_by_city' => 'nullable|boolean',
            'city_total_area' => 'nullable|numeric|min:0|max:99999.99',
            'city_apartment_count' => 'nullable|integer|min:1|max:999',
            'city_common_area_share' => 'nullable|numeric|min:0',
        ];
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
            ->where('owned_by_city', $data['owned_by_city'] ?? false)
            ->first();
    }

    private function convertToBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string)$value), ['true', '1', 'tak', 'yes', 'prawda', 'x']);
    }

    private function convertToDecimal($value): ?float
    {
        if ($value === '' || $value === null) {
            return null;
        }

        // Handle Polish decimal separator
        $value = str_replace(',', '.', (string)$value);

        return (float) $value;
    }

    private function normalizeApartmentType(string $type): string
    {
        $type = strtolower(trim($type));

        $mapping = [
            'mieszkaniowy' => 'residential',
            'mieszkalne' => 'residential',
            'residential' => 'residential',
            'komercyjny' => 'commercial',
            'commercial' => 'commercial',
            'usługowy' => 'commercial',
            'biurowy' => 'commercial',
            'mieszany' => 'mixed',
            'mixed' => 'mixed',
            'garaż' => 'garage',
            'garage' => 'garage',
            'piwnica' => 'storage',
            'storage' => 'storage',
            'magazyn' => 'storage',
        ];

        return $mapping[$type] ?? 'residential';
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

        \Log::info('=== APARTMENT IMPORT WITH CITY SUPPORT STARTED ===', [
            'file_path' => $filePath,
            'community_id' => $this->communityId,
            'options' => $options
        ]);

        return parent::import($filePath, $options);
    }
}
