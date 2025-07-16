<?php

namespace App\Services\Import;

use App\Models\Apartment;
use App\Models\Community;
use App\Enums\ApartmentType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApartmentImporter extends CsvImporter
{
    protected array $requiredColumns = [
        'community_id',
        'apartment_number'
    ];

    protected function getColumnMapping(): array
    {
        return [
            'wspólnota_id' => 'community_id',
            'community_id' => 'community_id',
            'numer_budynku' => 'building_number', 
            'building_number' => 'building_number',
            'numer_lokalu' => 'apartment_number',
            'apartment_number' => 'apartment_number',
            'kod' => 'code',
            'code' => 'code',
            'kod_domofonu' => 'intercom_code',
            'intercom_code' => 'intercom_code',
            'księga_wieczysta' => 'land_mortgage_register',
            'land_mortgage_register' => 'land_mortgage_register',
            'kw' => 'land_mortgage_register',
            'powierzchnia' => 'area',
            'area' => 'area',
            'powierzchnia_piwnicy' => 'basement_area',
            'basement_area' => 'basement_area',
            'powierzchnia_komórki' => 'storage_area',
            'storage_area' => 'storage_area',
            'udział_części_wspólnych' => 'common_area_share',
            'common_area_share' => 'common_area_share',
            'piętro' => 'floor',
            'floor' => 'floor',
            'współczynnik_windy' => 'elevator_fee_coefficient',
            'elevator_fee_coefficient' => 'elevator_fee_coefficient',
            'ma_piwnicę' => 'has_basement',
            'has_basement' => 'has_basement',
            'ma_komórkę' => 'has_storage',
            'has_storage' => 'has_storage',
            'typ_lokalu' => 'apartment_type',
            'apartment_type' => 'apartment_type',
            'opis_przeznaczenia' => 'usage_description',
            'usage_description' => 'usage_description',
            'osobne_wejście' => 'has_separate_entrance',
            'has_separate_entrance' => 'has_separate_entrance',
            'powierzchnia_użytkowa' => 'commercial_area',
            'commercial_area' => 'commercial_area',
            'komercyjny' => 'is_commercial', // Legacy support
            'is_commercial' => 'is_commercial', // Legacy support
        ];
    }

    protected function validateRow(array $data, int $rowNumber): bool
    {
        $validator = Validator::make($data, [
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
            'apartment_type' => ['nullable', Rule::enum(ApartmentType::class)],
            'usage_description' => 'nullable|string',
            'has_separate_entrance' => 'nullable|boolean',
            'commercial_area' => 'nullable|numeric|min:0',
            'is_commercial' => 'nullable|boolean', // Legacy
        ]);

        if ($validator->fails()) {
            $this->addError($rowNumber, 'Validation failed: ' . implode(', ', $validator->errors()->all()));
            return false;
        }

        // Check for duplicate apartments in the same community
        $existingApartment = Apartment::where('community_id', $data['community_id'])
            ->where('apartment_number', $data['apartment_number'])
            ->when(isset($data['building_number']), function ($query) use ($data) {
                return $query->where('building_number', $data['building_number']);
            })
            ->first();

        if ($existingApartment) {
            $this->addError($rowNumber, "Apartment {$data['apartment_number']} already exists in this community");
            return false;
        }

        return true;
    }

    protected function transformData(array $data): array
    {
        // Set default values
        $data['elevator_fee_coefficient'] = $data['elevator_fee_coefficient'] ?? 1.00;
        $data['apartment_type'] = $data['apartment_type'] ?? ApartmentType::RESIDENTIAL->value;
        
        // Handle legacy is_commercial field
        if (isset($data['is_commercial']) && !isset($data['apartment_type'])) {
            $data['apartment_type'] = $data['is_commercial'] ? 
                ApartmentType::COMMERCIAL->value : 
                ApartmentType::RESIDENTIAL->value;
        }

        // Transform boolean fields
        $booleanFields = ['has_basement', 'has_storage', 'has_separate_entrance', 'is_commercial'];
        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->convertToBoolean($data[$field]);
            }
        }

        // Clean up empty strings to null for optional numeric fields
        $optionalNumericFields = [
            'area', 'basement_area', 'storage_area', 'common_area_share', 
            'floor', 'elevator_fee_coefficient', 'commercial_area'
        ];
        foreach ($optionalNumericFields as $field) {
            if (isset($data[$field]) && ($data[$field] === '' || $data[$field] === null)) {
                $data[$field] = null;
            }
        }

        // Clean up empty strings to null for optional string fields
        $optionalStringFields = [
            'building_number', 'code', 'intercom_code', 'land_mortgage_register', 'usage_description'
        ];
        foreach ($optionalStringFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // Auto-generate code if not provided
        if (empty($data['code']) && !empty($data['apartment_number'])) {
            $data['code'] = $data['apartment_number'];
        }

        // Set is_commercial based on apartment_type for backward compatibility
        if (isset($data['apartment_type'])) {
            $data['is_commercial'] = in_array($data['apartment_type'], [
                ApartmentType::COMMERCIAL->value,
                ApartmentType::MIXED->value
            ]);
        }

        return $data;
    }

    protected function createRecord(array $data): bool
    {
        try {
            Apartment::create($data);
            $this->importStats['successful_imports']++;
            return true;
        } catch (\Exception $e) {
            $this->addError(0, "Failed to create apartment: " . $e->getMessage());
            return false;
        }
    }

    private function convertToBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        return in_array(strtolower((string)$value), ['true', '1', 'tak', 'yes', 'prawda', 'x']);
    }
}