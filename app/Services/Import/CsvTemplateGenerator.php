<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\Storage;

class CsvTemplateGenerator
{
    protected $templates = [
        'communities' => [
            'headers' => [
                'name',
                'full_name',
                'street',
                'postal_code',
                'city',
                'state',
                'regon',
                'tax_id',
                'manager_name',
                'manager_street',
                'manager_postal_code',
                'manager_city',
                'common_area_size',
                'apartments_area',
                'apartment_count',
                'has_elevator'
            ],
            'sample_data' => [
                [
                    'WM "Słoneczna"',
                    'Wspólnota Mieszkaniowa przy ul. Słonecznej 15',
                    'ul. Słoneczna 15',
                    '40-001',
                    'Katowice',
                    'śląskie',
                    '123456789',
                    '1234567890',
                    'Zarządca ABC Sp. z o.o.',
                    'ul. Zarządcza 1',
                    '40-001',
                    'Katowice',
                    '200.50',
                    '1500.75',
                    '24',
                    'tak'
                ]
            ]
        ],
        'apartments' => [
            'headers' => [
                'community_id',
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
                'commercial_area'
            ],
            'sample_data' => [
                [
                    '1', // community_id
                    '15', // building_number
                    '1', // apartment_number
                    '1', // code
                    '1', // intercom_code
                    'KA1K/12345678/9', // land_mortgage_register
                    '45.50', // area
                    '3.20', // basement_area
                    '2.50', // storage_area
                    '4.25', // common_area_share
                    '0', // floor (ground floor)
                    '1.00', // elevator_fee_coefficient
                    'tak', // has_basement
                    'tak', // has_storage
                    'residential', // apartment_type
                    '', // usage_description
                    'nie', // has_separate_entrance
                    '' // commercial_area
                ],
                [
                    '1', // community_id
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
                    '' // commercial_area
                ],
                [
                    '1', // community_id
                    '15', // building_number
                    'U1', // apartment_number (commercial unit)
                    'U1', // code
                    'U1', // intercom_code
                    'KA1K/12345678/11', // land_mortgage_register
                    '85.20', // area
                    '', // basement_area
                    '', // storage_area
                    '8.00', // common_area_share
                    '0', // floor
                    '0.00', // elevator_fee_coefficient
                    'nie', // has_basement
                    'nie', // has_storage
                    'commercial', // apartment_type
                    'Lokal usługowy - fryzjer', // usage_description
                    'tak', // has_separate_entrance
                    '85.20' // commercial_area
                ]
            ]
        ]
    ];

    public function generateTemplate(string $type, bool $includeSampleData = false): string
    {
        if (!isset($this->templates[$type])) {
            throw new \Exception("Unknown template type: {$type}");
        }

        $template = $this->templates[$type];
        $csvContent = [];

        // Add headers
        $csvContent[] = $this->arrayToCsvLine($template['headers']);

        // Add sample data if requested
        if ($includeSampleData && isset($template['sample_data'])) {
            foreach ($template['sample_data'] as $row) {
                $csvContent[] = $this->arrayToCsvLine($row);
            }
        }

        return implode("\n", $csvContent);
    }

    public function downloadTemplate(string $type, bool $includeSampleData = false): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $content = $this->generateTemplate($type, $includeSampleData);
        $filename = "template_{$type}_" . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function arrayToCsvLine(array $data): string
    {
        return '"' . implode('","', array_map(function ($field) {
            return str_replace('"', '""', (string) $field);
        }, $data)) . '"';
    }

    public function getAvailableTemplates(): array
    {
        return array_keys($this->templates);
    }
}
