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
            2 => 'address_street',
            3 => 'address_postal_code',
            4 => 'address_city',
            5 => 'regon',
            6 => 'tax_id',
        ];
    }

    protected function getValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'address_street' => ['required', 'string', 'max:255'],
            'address_postal_code' => ['required', 'string', 'max:10'],
            'address_city' => ['required', 'string', 'max:50'],
            'regon' => ['required', 'string', 'max:20', 'unique:communities,regon'],
            'tax_id' => ['required', 'string', 'max:20'],
        ];
    }

    protected function getModelClass(): string
    {
        return Community::class;
    }

    protected function transformData(array $data): array
    {
        // Ensure we have an array
        if (!is_array($data)) {
            return [];
        }

        // Set required defaults
        $data['is_active'] = true;
        $data['color'] = '#3b82f6';
        
        // Set defaults for required fields that might be missing
        $data['manager_name'] = $data['manager_name'] ?? 'Default Manager';
        $data['manager_address_street'] = $data['manager_address_street'] ?? ($data['address_street'] ?? '');
        $data['manager_address_postal_code'] = $data['manager_address_postal_code'] ?? ($data['address_postal_code'] ?? '');
        $data['manager_address_city'] = $data['manager_address_city'] ?? ($data['address_city'] ?? '');
        $data['common_area_size'] = $data['common_area_size'] ?? 0.00;
        $data['apartments_area'] = $data['apartments_area'] ?? 0.00;

        return $data;
    }
}