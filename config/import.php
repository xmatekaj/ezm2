<?php
// config/import.php - Configuration file for import settings

return [
    
    /*
    |--------------------------------------------------------------------------
    | Default Import Settings
    |--------------------------------------------------------------------------
    */
    
    'defaults' => [
        'delimiter' => ';',
        'decimal_separator' => ',',
        'encoding' => 'UTF-8',
        'quote_char' => '"',
        'escape_char' => '"',
        'skip_header' => true,
        'trim_whitespace' => true,
        'skip_empty_rows' => true,
        'batch_size' => 500,
        'auto_detect_delimiter' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Polish CSV Settings
    |--------------------------------------------------------------------------
    | Common settings for Polish CSV files
    */
    
    'polish' => [
        'delimiter' => ';',
        'decimal_separator' => ',',
        'encoding' => 'Windows-1250',
        'quote_char' => '"',
    ],

    /*
    |--------------------------------------------------------------------------
    | International CSV Settings
    |--------------------------------------------------------------------------
    | Common settings for international CSV files
    */
    
    'international' => [
        'delimiter' => ',',
        'decimal_separator' => '.',
        'encoding' => 'UTF-8',
        'quote_char' => '"',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excel Export Settings
    |--------------------------------------------------------------------------
    | Settings for files exported from Excel
    */
    
    'excel' => [
        'delimiter' => "\t",  // Tab-separated
        'decimal_separator' => ',',
        'encoding' => 'UTF-8',
        'quote_char' => '"',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Formats
    |--------------------------------------------------------------------------
    */
    
    'supported_delimiters' => [
        ',' => 'Comma (,)',
        ';' => 'Semicolon (;)',
        "\t" => 'Tab',
        '|' => 'Pipe (|)',
    ],

    'supported_encodings' => [
        'UTF-8' => 'UTF-8 (Universal)',
        'Windows-1250' => 'Windows-1250 (Polish)',
        'ISO-8859-2' => 'ISO-8859-2 (Central Europe)',
        'ISO-8859-1' => 'ISO-8859-1 (Western Europe)',
    ],

    'supported_decimal_separators' => [
        '.' => 'Dot (.)',
        ',' => 'Comma (,)',
    ],
];

/*
=============================================================================
CSV FORMAT EXAMPLES
=============================================================================

1. POLISH FORMAT WITH SEMICOLON DELIMITER AND COMMA DECIMAL SEPARATOR:
apartment_number;area;elevator_coefficient;floor;name
123;45,5;1,25;2;Jan Kowalski
124;52,3;1,00;2;Anna Nowak
125;38,7;1,50;1;Piotr Wiśniewski

2. INTERNATIONAL FORMAT WITH COMMA DELIMITER AND DOT DECIMAL SEPARATOR:
apartment_number,area,elevator_coefficient,floor,name
123,45.5,1.25,2,"Jan Kowalski"
124,52.3,1.00,2,"Anna Nowak"
125,38.7,1.50,1,"Piotr Wiśniewski"

3. TAB-SEPARATED VALUES (TSV) - EXCEL EXPORT:
apartment_number	area	elevator_coefficient	floor	name
123	45,5	1,25	2	Jan Kowalski
124	52,3	1,00	2	Anna Nowak
125	38,7	1,50	1	Piotr Wiśniewski

4. UNQUOTED VALUES WITH SEMICOLON DELIMITER:
apartment_number;area;elevator_coefficient;floor;name;address
123;45,5;1,25;2;Jan Kowalski;ul. Słoneczna 15
124;52,3;1,00;2;Anna Nowak;ul. Kwiatowa 8
125;38,7;1,50;1;Piotr Wiśniewski;ul. Zielona 22

5. VALUES WITH QUOTES ONLY WHEN NECESSARY:
apartment_number;area;elevator_coefficient;floor;name;notes
123;45,5;1,25;2;Jan Kowalski;"Apartament z balkonem, południowa strona"
124;52,3;1,00;2;Anna Nowak;Standardowe wyposażenie
125;38,7;1,50;1;Piotr Wiśniewski;"Mieszkanie narożne, dodatkowe okno"

6. MIXED DELIMITERS (AUTO-DETECTION NEEDED):
apartment_number|area|elevator_coefficient|floor|name
123|45,5|1,25|2|Jan Kowalski
124|52,3|1,00|2|Anna Nowak
125|38,7|1,50|1|Piotr Wiśniewski

=============================================================================
USAGE IN APPLICATION
=============================================================================

// Using predefined configurations
$polishConfig = config('import.polish');
$internationalConfig = config('import.international');

// Import with Polish settings
$importManager->import('apartments', $filePath, $polishConfig);

// Import with auto-detection
$autoConfig = [
    'auto_detect_delimiter' => true,
    'decimal_separator' => 'auto',
    'encoding' => 'UTF-8',
];
$importManager->import('apartments', $filePath, $autoConfig);

=============================================================================
COMMAND LINE EXAMPLES
=============================================================================

# Polish CSV with semicolon delimiter
php artisan import:data apartments file.csv --delimiter=semicolon --decimal-separator=, --encoding=Windows-1250

# International CSV with comma delimiter  
php artisan import:data apartments file.csv --delimiter=comma --decimal-separator=. --encoding=UTF-8

# Tab-separated values (Excel export)
php artisan import:data apartments file.tsv --delimiter=tab --decimal-separator=,

# Unquoted CSV file
php artisan import:data apartments file.csv --delimiter=semicolon --no-quotes

# Auto-detect everything
php artisan import:data apartments file.csv --auto-detect

# Preview before importing
php artisan import:data apartments file.csv --preview --auto-detect

# Validate file structure
php artisan import:data apartments file.csv --validate --delimiter=semicolon

=============================================================================
*/