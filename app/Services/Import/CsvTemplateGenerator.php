<?php

// app/Services/Import/CsvTemplateGenerator.php
namespace App\Services\Import;

use Illuminate\Support\Facades\Storage;

class CsvTemplateGenerator
{
    protected $templates = [
        'communities' => [
            'headers' => [
                'name',
                'full_name',
                'address_street',
                'address_postal_code',
                'address_city',
                'address_state',
                'regon',
                'tax_id',
                'manager_name',
                'manager_address_street',
                'manager_address_postal_code',
                'manager_address_city',
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
                    'ul. Zarządu 1',
                    '40-002',
                    'Katowice',
                    '250.50',
                    '1500.75',
                    '24',
                    'tak'
                ]
            ]
        ],
        
        'apartments' => [
            'headers' => [
                'community_name',
                'building_number',
                'apartment_number',
                'area',
                'basement_area',
                'storage_area',
                'heated_area',
                'common_area_share',
                'floor',
                'elevator_fee_coefficient',
                'has_basement',
                'has_storage',
                'is_owned',
                'is_commercial'
            ],
            'sample_data' => [
                [
                    'WM "Słoneczna"',
                    '1',
                    '1',
                    '45.50',
                    '3.20',
                    '2.50',
                    '45.50',
                    '4.25',
                    '0',
                    '1.00',
                    'tak',
                    'tak',
                    'tak',
                    'nie'
                ],
                [
                    'WM "Słoneczna"',
                    '1',
                    '2',
                    '62.30',
                    '',
                    '2.50',
                    '62.30',
                    '5.85',
                    '1',
                    '1.00',
                    'nie',
                    'tak',
                    'tak',
                    'nie'
                ]
            ]
        ],
        
        'people' => [
            'headers' => [
                'first_name',
                'last_name',
                'email',
                'phone',
                'correspondence_address_street',
                'correspondence_address_postal_code',
                'correspondence_address_city',
                'ownership_share',
                'notes'
            ],
            'sample_data' => [
                [
                    'Jan',
                    'Kowalski',
                    'jan.kowalski@example.com',
                    '+48 123 456 789',
                    'ul. Mieszkańcowa 10/5',
                    '40-001',
                    'Katowice',
                    '100.00',
                    'Właściciel mieszkania'
                ]
            ]
        ],
        
        'water_meters' => [
            'headers' => [
                'community_name',
                'apartment_number',
                'meter_number',
                'transmitter_number',
                'installation_date',
                'meter_expiry_date',
                'transmitter_installation_date',
                'transmitter_expiry_date'
            ],
            'sample_data' => [
                [
                    'WM "Słoneczna"',
                    '1',
                    '100001',
                    '200001',
                    '2023-01-15',
                    '2029-01-15',
                    '2023-01-15',
                    '2028-01-15'
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
        $csv = '';

        // Add headers
        $csv .= implode(',', $this->escapeHeadersForCsv($template['headers'])) . "\n";

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
        ];
    }

    protected function getTemplateDescription(string $type): string
    {
        return match($type) {
            'communities' => 'Import housing communities with their basic information and management details.',
            'apartments' => 'Import apartments for a specific community. Requires community to exist first.',
            'people' => 'Import people/residents with their contact and ownership information.',
            'water_meters' => 'Import water meters assigned to apartments. Requires apartments to exist first.',
            default => "Import {$type} data."
        };
    }

    protected function getRequiredFields(string $type): array
    {
        return match($type) {
            'communities' => ['name', 'full_name', 'address_street', 'address_postal_code', 'address_city', 'regon', 'tax_id'],
            'apartments' => ['apartment_number', 'community_name'],
            'people' => ['first_name', 'last_name'],
            'water_meters' => ['community_name', 'apartment_number', 'meter_number', 'installation_date', 'meter_expiry_date'],
            default => []
        };
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
            if (is_null($value)) {
                return '';
            }
            
            $value = (string) $value;
            
            // If value contains comma, quote, or newline, wrap in quotes
            if (strpos($value, ',') !== false || 
                strpos($value, '"') !== false || 
                strpos($value, "\n") !== false) {
                $value = '"' . str_replace('"', '""', $value) . '"';
            }
            
            return $value;
        }, $row);
    }
}