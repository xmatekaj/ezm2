<?php

namespace App\Services\Import;

use Illuminate\Http\Response;

class MultilingualCsvTemplateGenerator
{
    protected string $locale = 'pl';

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Get apartment column definitions with multilingual support
     */
    protected function getApartmentColumnDefinitions(): array
    {
        return [
            // Column definitions: [model_field, polish_name, english_name, required, type, example]
            ['building_number', 'numer_budynku', 'building_number', false, 'string', '29'],
            ['apartment_number', 'numer_lokalu', 'apartment_number', true, 'string', '1'],
            ['code', 'kod', 'code', false, 'string', '0001'],
            ['intercom_code', 'kod_domofonu', 'intercom_code', false, 'string', '8863'],
            ['land_mortgage_register', 'księga_wieczysta', 'land_mortgage_register', false, 'string', 'KA1S / 00100366 / 3'],
            ['area', 'powierzchnia', 'area', false, 'decimal', '43,46'],
            ['basement_area', 'powierzchnia_piwnicy', 'basement_area', false, 'decimal', '5,40'],
            ['storage_area', 'powierzchnia_komórki', 'storage_area', false, 'decimal', '2,50'],
            ['common_area_share', 'udział_części_wspólnych', 'common_area_share', false, 'decimal', '1,82%'],
            ['floor', 'piętro', 'floor', false, 'integer', '2'],
            ['elevator_fee_coefficient', 'współczynnik_windy', 'elevator_fee_coefficient', false, 'decimal', '1,0'],
            ['has_basement', 'ma_piwnicę', 'has_basement', false, 'boolean', 'tak'],
            ['has_storage', 'ma_komórkę', 'has_storage', false, 'boolean', 'nie'],
            ['apartment_type', 'typ_lokalu', 'apartment_type', false, 'string', 'mieszkaniowy'],
            ['usage_description', 'opis_przeznaczenia', 'usage_description', false, 'string', 'mieszkanie'],
            ['has_separate_entrance', 'osobne_wejście', 'has_separate_entrance', false, 'boolean', 'nie'],
            ['commercial_area', 'powierzchnia_użytkowa', 'commercial_area', false, 'decimal', ''],
        ];
    }

    /**
     * Generate CSV template for apartments
     */
    public function generateApartmentTemplate(string $format = 'minimal', bool $withExamples = false): string
    {
        $columns = $this->getApartmentColumnDefinitions();
        $selectedColumns = $this->filterColumnsByFormat($columns, $format);
        
        $csv = '';
        
        // Add header row
        $headers = [];
        foreach ($selectedColumns as $column) {
            $headers[] = $this->getColumnName($column);
        }
        $csv .= implode(';', $headers) . "\n";
        
        // Add example rows if requested
        if ($withExamples) {
            $csv .= $this->generateExampleRows($selectedColumns);
        }
        
        return $csv;
    }

    /**
     * Filter columns based on format
     */
    protected function filterColumnsByFormat(array $columns, string $format): array
    {
        switch ($format) {
            case 'minimal':
                return array_filter($columns, function($col) {
                    return in_array($col[0], ['building_number', 'apartment_number']);
                });
                
            case 'basic':
                return array_filter($columns, function($col) {
                    return in_array($col[0], [
                        'building_number', 'apartment_number', 'area', 'floor', 'has_basement', 'has_storage'
                    ]);
                });
                
            case 'extended':
                return array_filter($columns, function($col) {
                    return in_array($col[0], [
                        'building_number', 'apartment_number', 'code', 'area', 'basement_area', 
                        'storage_area', 'floor', 'has_basement', 'has_storage', 'apartment_type'
                    ]);
                });
                
            case 'full':
            default:
                return $columns;
        }
    }

    /**
     * Get column name based on locale
     */
    protected function getColumnName(array $column): string
    {
        // column structure: [model_field, polish_name, english_name, required, type, example]
        return $this->locale === 'pl' ? $column[1] : $column[2];
    }

    /**
     * Generate example data rows
     */
    protected function generateExampleRows(array $columns): string
    {
        $examples = [
            // Example 1
            [
                'building_number' => '29',
                'apartment_number' => '1',
                'code' => '0001',
                'intercom_code' => '8863',
                'land_mortgage_register' => 'KA1S / 00100366 / 3',
                'area' => '43,46',
                'basement_area' => '5,40',
                'storage_area' => '',
                'common_area_share' => '1,82%',
                'floor' => '0',
                'elevator_fee_coefficient' => '1,0',
                'has_basement' => 'tak',
                'has_storage' => 'nie',
                'apartment_type' => 'mieszkaniowy',
                'usage_description' => '',
                'has_separate_entrance' => 'nie',
                'commercial_area' => '',
            ],
            // Example 2
            [
                'building_number' => '29',
                'apartment_number' => '2',
                'code' => '0002',
                'intercom_code' => '5494',
                'land_mortgage_register' => 'KA1S / 00074419 / 8',
                'area' => '45,20',
                'basement_area' => '6,10',
                'storage_area' => '2,50',
                'common_area_share' => '1,95%',
                'floor' => '2',
                'elevator_fee_coefficient' => '1,0',
                'has_basement' => 'tak',
                'has_storage' => 'tak',
                'apartment_type' => 'mieszkaniowy',
                'usage_description' => '',
                'has_separate_entrance' => 'nie',
                'commercial_area' => '',
            ],
            // Example 3 - minimal data
            [
                'building_number' => '31',
                'apartment_number' => '3',
                'code' => '',
                'intercom_code' => '',
                'land_mortgage_register' => '',
                'area' => '',
                'basement_area' => '',
                'storage_area' => '',
                'common_area_share' => '',
                'floor' => '',
                'elevator_fee_coefficient' => '',
                'has_basement' => '',
                'has_storage' => '',
                'apartment_type' => '',
                'usage_description' => '',
                'has_separate_entrance' => '',
                'commercial_area' => '',
            ]
        ];

        $csv = '';
        foreach ($examples as $example) {
            $row = [];
            foreach ($columns as $column) {
                $fieldName = $column[0]; // model_field
                $row[] = $example[$fieldName] ?? '';
            }
            $csv .= implode(';', $row) . "\n";
        }

        return $csv;
    }

    /**
     * Download template as CSV file
     */
    public function downloadTemplate(string $type = 'apartments', string $format = 'basic', bool $withExamples = true): Response
    {
        if ($type !== 'apartments') {
            throw new \InvalidArgumentException("Unsupported template type: {$type}");
        }

        $csv = $this->generateApartmentTemplate($format, $withExamples);
        
        $filename = $this->generateFilename($type, $format);
        
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($csv),
        ]);
    }

    /**
     * Generate filename for template
     */
    protected function generateFilename(string $type, string $format): string
    {
        $localePrefix = $this->locale === 'pl' ? 'pl_' : 'en_';
        $timestamp = date('Y-m-d');
        
        $typeTranslations = [
            'apartments' => $this->locale === 'pl' ? 'lokale' : 'apartments'
        ];
        
        $formatTranslations = [
            'minimal' => $this->locale === 'pl' ? 'minimalna' : 'minimal',
            'basic' => $this->locale === 'pl' ? 'podstawowa' : 'basic', 
            'extended' => $this->locale === 'pl' ? 'rozszerzona' : 'extended',
            'full' => $this->locale === 'pl' ? 'pelna' : 'full'
        ];

        $typeName = $typeTranslations[$type] ?? $type;
        $formatName = $formatTranslations[$format] ?? $format;
        
        return "{$localePrefix}szablon_{$typeName}_{$formatName}_{$timestamp}.csv";
    }

    /**
     * Get template information for UI
     */
    public function getTemplateInfo(string $format = 'basic'): array
    {
        $columns = $this->getApartmentColumnDefinitions();
        $selectedColumns = $this->filterColumnsByFormat($columns, $format);
        
        $info = [
            'format' => $format,
            'locale' => $this->locale,
            'column_count' => count($selectedColumns),
            'columns' => [],
            'description' => $this->getFormatDescription($format),
            'filename_example' => $this->generateFilename('apartments', $format)
        ];
        
        foreach ($selectedColumns as $column) {
            $info['columns'][] = [
                'field' => $column[0],
                'name' => $this->getColumnName($column),
                'required' => $column[3],
                'type' => $column[4],
                'example' => $column[5]
            ];
        }
        
        return $info;
    }

    /**
     * Get format description
     */
    protected function getFormatDescription(string $format): string
    {
        if ($this->locale === 'pl') {
            return match($format) {
                'minimal' => 'Minimalna wersja - tylko numer budynku i lokalu',
                'basic' => 'Podstawowa wersja - najczęściej używane pola',
                'extended' => 'Rozszerzona wersja - dodatkowe szczegóły',
                'full' => 'Pełna wersja - wszystkie dostępne pola',
                default => 'Szablon importu lokali'
            };
        } else {
            return match($format) {
                'minimal' => 'Minimal version - building and apartment numbers only',
                'basic' => 'Basic version - most commonly used fields',
                'extended' => 'Extended version - additional details',
                'full' => 'Full version - all available fields',
                default => 'Apartment import template'
            };
        }
    }

    /**
     * Get available formats
     */
    public function getAvailableFormats(): array
    {
        return [
            'minimal' => $this->getFormatDescription('minimal'),
            'basic' => $this->getFormatDescription('basic'),
            'extended' => $this->getFormatDescription('extended'),
            'full' => $this->getFormatDescription('full'),
        ];
    }
}