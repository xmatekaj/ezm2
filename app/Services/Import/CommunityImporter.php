<?php

namespace App\Services\Import;

use App\Models\Community;

class CommunityImporter extends CsvImporter
{
    protected function getColumnMapping(): array
    {
        return [
            0 => 'name',
            1 => 'full_name',
            2 => 'internal_code',
            3 => 'address_street',
            4 => 'address_postal_code',
            5 => 'address_city',
            6 => 'address_state',
            7 => 'regon',
            8 => 'tax_id',
            9 => 'total_area',
            10 => 'apartments_area',
            11 => 'apartment_count',
            12 => 'staircase_count',
            13 => 'has_elevator',
            14 => 'residential_water_meters',
            15 => 'main_water_meters',
        ];
    }

    protected function getValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'internal_code' => ['nullable', 'string', 'max:255'],
            'address_street' => ['required', 'string', 'max:255'],
            'address_postal_code' => ['required', 'string', 'max:10'],
            'address_city' => ['required', 'string', 'max:50'],
            'address_state' => ['required', 'string', 'max:50'],
            // Made optional - no longer unique in import
            'regon' => ['nullable', 'string', 'max:20'],
            'tax_id' => ['nullable', 'string', 'max:20'],
            // All technical parameters are now optional
            'total_area' => ['nullable', 'numeric', 'min:0'],
            'apartments_area' => ['nullable', 'numeric', 'min:0'],
            'apartment_count' => ['nullable', 'integer', 'min:0'],
            'staircase_count' => ['nullable', 'integer', 'min:0'],
            'has_elevator' => ['nullable', 'boolean'],
            'residential_water_meters' => ['nullable', 'integer', 'min:0'],
            'main_water_meters' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function getModelClass(): string
    {
        return Community::class;
    }

    protected function transformData(array $data): array
    {
        if (!is_array($data)) {
            return [];
        }

        // Set required defaults
        $data['is_active'] = $data['is_active'] ?? true;
        $data['color'] = $data['color'] ?? '#3b82f6';

        // Transform boolean fields
        if (isset($data['has_elevator'])) {
            $data['has_elevator'] = in_array(strtolower($data['has_elevator']), ['true', '1', 'tak', 'yes']);
        }

        // Clean up empty strings to null for optional numeric fields
        $optionalNumericFields = ['total_area', 'apartments_area', 'apartment_count', 'staircase_count', 'residential_water_meters', 'main_water_meters'];
        foreach ($optionalNumericFields as $field) {
            if (isset($data[$field]) && ($data[$field] === '' || $data[$field] === null)) {
                $data[$field] = null;
            }
        }

        // Clean up empty strings to null for optional string fields
        $optionalStringFields = ['regon', 'tax_id', 'internal_code'];
        foreach ($optionalStringFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }
}
