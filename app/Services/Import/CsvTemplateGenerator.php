<?php

namespace App\Services\Import;

class CsvTemplateGenerator
{
    protected array $templates = [
        'apartments' => [
            'headers' => [
                'building_number',
                'apartment_number',
                'code',
                'intercom_code',
                'land_mortgage_register',
                'area',
                'basement_area',
                'storage_area',
                'common_area_share',
                'floor',
                'elevator_fee_coefficient',
                'has_basement',
                'has_storage',
                'apartment_type',
                'usage_description',
                'has_separate_entrance',
                'commercial_area',
                // City ownership fields
                'owned_by_city',
                'city_total_area',
                'city_apartment_count',
                'city_common_area_share',
            ],
            'sample_data' => [
                [
                    '15', // building_number
                    '1', // apartment_number
                    '1', // code
                    '1', // intercom_code
                    'KA1K/12345678/9', // land_mortgage_register
                    '48.50', // area
                    '12.30', // basement_area
                    '5.20', // storage_area
                    '4.85', // common_area_share
                    '0', // floor (ground floor)
                    '1.00', // elevator_fee_coefficient
                    'tak', // has_basement
                    'tak', // has_storage
                    'residential', // apartment_type
                    '', // usage_description
                    'nie', // has_separate_entrance
                    '', // commercial_area
                    'nie', // owned_by_city
                    '', // city_total_area
                    '', // city_apartment_count
                    '', // city_common_area_share
                ],
                [
                    '15', // building_number
                    '2', // apartment_number
                    '2', // code
                    '2', // intercom_code
                    'KA1K/12345678/10', // land_mortgage_register
                    '62.30', // area
                    '', // basement_area
                    '', // storage_area
                    '5.85', // common_area_share
                    '1', // floor
                    '1.20', // elevator_fee_coefficient
                    'nie', // has_basement
                    'nie', // has_storage
                    'residential', // apartment_type
                    '', // usage_description
                    'nie', // has_separate_entrance
                    '', // commercial_area
                    'nie', // owned_by_city
                    '', // city_total_area
                    '', // city_apartment_count
                    '', // city_common_area_share
                ],
                [
                    '15', // building_number
                    'MIASTO', // apartment_number - special identifier for city apartments
                    'MIASTO', // code
                    '', // intercom_code
                    'KA1K/12345678/11', // land_mortgage_register
                    '', // area (individual areas unknown)
                    '', // basement_area
                    '', // storage_area
                    '', // common_area_share (will use city_common_area_share)
                    '0', // floor
                    '1.00', // elevator_fee_coefficient
                    'nie', // has_basement
                    'nie', // has_storage
                    'residential', // apartment_type
                    'Lokale komunalne miasta', // usage_description
                    'nie', // has_separate_entrance
                    '', // commercial_area
                    'tak', // owned_by_city
                    '180.75', // city_total_area (total for all city apartments)
                    '3', // city_apartment_count (3 apartments owned by city)
                    '12.50', // city_common_area_share (combined share for all city apartments)
                ],
                [
                    '16', // building_number
                    'U1', // apartment_number (commercial unit)
                    'U1', // code
                    'U1', // intercom_code
                    'KA1K/12345678/12', // land_mortgage_register
                    '85.20', // area
                    '', // basement_area
                    '', // storage_area
                    '7.95', // common_area_share
                    '0', // floor
                    '1.00', // elevator_fee_coefficient
                    'nie', // has_basement
                    'nie', // has_storage
                    'commercial', // apartment_type
                    'Sklep spożywczy', // usage_description
                    'tak', // has_separate_entrance
                    '85.20', // commercial_area
                    'nie', // owned_by_city
                    '', // city_total_area
                    '', // city_apartment_count
                    '', // city_common_area_share
                ]
            ]
        ],
        // Other templates remain the same...
        'communities' => [
            'headers' => [
                'name',
                'full_name',
                'street',
                'postal_code',
                'city',
                'regon',
                'tax_id',
                'phone',
                'email',
                'management_company',
                'is_active'
            ],
            'sample_data' => [
                [
                    'WSM Kowalskiego',
                    'Wspólnota Mieszkaniowa ul. Kowalskiego 15',
                    'ul. Kowalskiego 15',
                    '40-123',
                    'Katowice',
                    '123456789',
                    '1234567890',
                    '+48 32 123 45 67',
                    'kontakt@wsm-kowalskiego.pl',
                    'Zarząd WSM',
                    'tak'
                ]
            ]
        ]
    ];

    public function generateTemplate(string $type, bool $includeSampleData = true): string
    {
        if (!isset($this->templates[$type])) {
            throw new \InvalidArgumentException("Unknown template type: {$type}");
        }

        $template = $this->templates[$type];

        // Create CSV headers
        $csv = implode(',', $this->escapeHeadersForCsv($template['headers'])) . "\n";

        // Add sample data if requested
        if ($includeSampleData && isset($template['sample_data'])) {
            foreach ($template['sample_data'] as $row) {
                $csv .= implode(',', $this->escapeRowForCsv($row)) . "\n";
            }
        }

        return $csv;
    }

    public function downloadTemplate(string $type, bool $includeSampleData = true): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $csv = $this->generateTemplate($type, $includeSampleData);
        $filename = "template_{$type}_" . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function getAvailableTemplates(): array
    {
        return array_keys($this->templates);
    }

    public function getTemplateInfo(string $type): array
    {
        if (!isset($this->templates[$type])) {
            throw new \InvalidArgumentException("Unknown template type: {$type}");
        }

        $template = $this->templates[$type];

        return [
            'type' => $type,
            'headers' => $template['headers'],
            'column_count' => count($template['headers']),
            'sample_rows' => count($template['sample_data'] ?? []),
            'description' => $this->getTemplateDescription($type),
            'required_fields' => $this->getRequiredFields($type),
            'city_fields_info' => $this->getCityFieldsInfo($type),
        ];
    }

    protected function getTemplateDescription(string $type): string
    {
        return match($type) {
            'communities' => 'Import housing communities with their basic information and management details.',
            'apartments' => 'Import apartments for a specific community. Supports both private and city-owned apartments. For city apartments, you can specify total area and apartment count when individual areas are unknown.',
            'people' => 'Import people/residents with their contact and ownership information.',
            'water_meters' => 'Import water meters assigned to apartments. Requires apartments to exist first.',
            default => "Import {$type} data."
        };
    }

    protected function getRequiredFields(string $type): array
    {
        return match($type) {
            'communities' => ['name', 'full_name', 'street', 'postal_code', 'city', 'regon', 'tax_id'],
            'apartments' => ['apartment_number'],
            'people' => ['first_name', 'last_name'],
            'water_meters' => ['community_name', 'apartment_number', 'meter_number', 'installation_date', 'meter_expiry_date'],
            default => []
        };
    }

    protected function getCityFieldsInfo(string $type): array
    {
        if ($type !== 'apartments') {
            return [];
        }

        return [
            'description' => 'City ownership fields allow importing apartments owned by the municipality',
            'fields' => [
                'owned_by_city' => [
                    'description' => 'Mark as city-owned apartment (tak/nie)',
                    'required' => false,
                    'note' => 'Can be auto-detected from apartment_number or other fields containing "miasto", "city", etc.'
                ],
                'city_total_area' => [
                    'description' => 'Total area of all city apartments when individual areas are unknown',
                    'required' => 'Required if owned_by_city=true and individual area is not specified',
                    'note' => 'Use this when you only know the combined area of multiple city apartments'
                ],
                'city_apartment_count' => [
                    'description' => 'Number of apartments this record represents',
                    'required' => false,
                    'note' => 'Defaults to 1 if not specified. Use >1 when this record represents multiple city apartments'
                ],
                'city_common_area_share' => [
                    'description' => 'Common area share for all city apartments combined',
                    'required' => 'Required if owned_by_city=true',
                    'note' => 'This is the total common area share for all city apartments, not per apartment'
                ]
            ],
            'examples' => [
                'single_city_apartment' => [
                    'apartment_number' => 'M1',
                    'owned_by_city' => 'tak',
                    'area' => '45.50',
                    'city_apartment_count' => '1',
                    'city_common_area_share' => '3.25'
                ],
                'multiple_city_apartments' => [
                    'apartment_number' => 'MIASTO',
                    'owned_by_city' => 'tak',
                    'city_total_area' => '180.75',
                    'city_apartment_count' => '3',
                    'city_common_area_share' => '12.50'
                ]
            ]
        ];
    }

    protected function escapeHeadersForCsv(array $headers): array
    {
        return array_map(function ($header) {
            return '"' . str_replace('"', '""', $header) . '"';
        }, $headers);
    }

    protected function escapeRowForCsv(array $row): array
    {
        return array_map(function ($value) {
            if ($value === null || $value === '') {
                return '';
            }

            $value = (string) $value;

            // If value contains comma, quote, or newline, wrap in quotes
            if (strpos($value, ',') !== false ||
                strpos($value, '"') !== false ||
                strpos($value, "\n") !== false) {
                return '"' . str_replace('"', '""', $value) . '"';
            }

            return $value;
        }, $row);
    }
}
