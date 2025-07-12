# CSV Import System Documentation

## Overview

The EZM application includes a comprehensive CSV import system that allows administrators to import various types of data from CSV files. The system is designed to be:

- **Generic and Extensible**: Easy to add new import types
- **Robust**: Handles errors gracefully with detailed reporting
- **User-Friendly**: Provides templates and mapping suggestions
- **Scalable**: Supports batch processing and background jobs

## Supported Import Types

### 1. Communities
Import housing communities with their basic information.

**Required Fields:**
- `name` - Community name
- `full_name` - Full legal name
- `street` - Street address
- `postal_code` - Postal code
- `city` - City name
- `regon` - REGON number (unique)
- `tax_id` - Tax identification number

**Optional Fields:**
- `state`, `manager_name`, `manager_street`, `manager_postal_code`, `manager_city`
- `common_area_size`, `apartments_area`, `apartment_count`, `has_elevator`

### 2. Apartments
Import apartments for specific communities.

**Required Fields:**
- `apartment_number` - Apartment number
- `community_name` - Name of the community (for lookup)

**Optional Fields:**
- `building_number`, `area`, `basement_area`, `storage_area`, `heated_area`
- `common_area_share`, `floor`, `elevator_fee_coefficient`
- `has_basement`, `has_storage`, `is_owned`, `is_commercial`

### 3. People
Import residents and property owners.

**Required Fields:**
- `first_name` - First name
- `last_name` - Last name

**Optional Fields:**
- `email`, `phone`, `street`, `postal_code`, `city`
- `ownership_share`, `notes`

### 4. Water Meters
Import water meter installations.

**Required Fields:**
- `community_name` - Community name (for lookup)
- `apartment_number` - Apartment number (for lookup)
- `meter_number` - Unique meter number
- `installation_date` - Installation date
- `meter_expiry_date` - Meter expiry date

**Optional Fields:**
- `transmitter_number`, `transmitter_installation_date`, `transmitter_expiry_date`

## How to Use

### 1. Admin Panel Import

1. Navigate to **System > Data Import** in the admin panel
2. Click **New Import**
3. Select the import type
4. Upload your CSV file
5. Configure import options (delimiter, encoding, etc.)
6. Click **Create** to start the import

### 2. Download Templates

1. Go to **System > Data Import**
2. Click **Download Templates**
3. Select the template type
4. Choose whether to include sample data
5. Download and use as a reference

### 3. Command Line Import

```bash
# Import communities
php artisan import:data communities storage/app/communities.csv --save-job

# Import apartments for specific community
php artisan import:data apartments storage/app/apartments.csv --community-id=1

# Import with custom options
php artisan import:data people storage/app/people.csv \
  --delimiter=";" \
  --encoding="ISO-8859-2" \
  --batch-size=100 \
  --skip-header
```

## CSV Format Requirements

### File Format
- **Encoding**: UTF-8 recommended (ISO-8859-1, Windows-1250 supported)
- **Delimiter**: Comma (`,`) by default (semicolon `;` supported)
- **Header Row**: First row should contain column names
- **Boolean Values**: Use `tak`/`nie`, `yes`/`no`, `true`/`false`, or `1`/`0`

### Example CSV Structure

```csv
name,full_name,street,postal_code,city,regon,tax_id
"WM Słoneczna","Wspólnota Mieszkaniowa przy ul. Słonecznej","ul. Słoneczna 15","40-001","Katowice","123456789","1234567890"
```

### Data Validation

Each import type has specific validation rules:

- **Required fields** must be present and non-empty
- **Unique fields** (like REGON) are checked for duplicates
- **Numeric fields** are validated for proper format
- **Date fields** accept formats: YYYY-MM-DD, DD.MM.YYYY, DD/MM/YYYY
- **Boolean fields** accept various formats as mentioned above

## Error Handling

### Import Statistics
Each import provides detailed statistics:
- Total rows processed
- Successful imports
- Failed imports
- Skipped rows
- Detailed error messages

### Error Reports
- Downloadable error reports in text format
- Row-by-row error descriptions
- Validation failure details

### Recovery Options
- Failed imports can be retried
- Partial imports are supported
- Transaction rollback on critical errors

## Advanced Features

### Column Mapping
The system can automatically suggest column mappings based on header names:

```php
// Automatic mapping examples
'nazwa' -> 'name'
'mieszkanie' -> 'apartment_number'
'powierzchnia' -> 'area'
```

### Batch Processing
Large files are processed in configurable batches:
- Default batch size: 500 rows
- Configurable per import
- Memory-efficient processing

### Background Processing
For large imports, use queue jobs:

```php
ProcessImportJob::dispatch($importJob);
```

### Custom Import Types

Create custom importers by extending the base class:

```php
class CustomImporter extends CsvImporter
{
    protected function getColumnMapping(): array
    {
        return [
            'csv_column' => 'model_field',
            // ... more mappings
        ];
    }

    protected function getValidationRules(): array
    {
        return [
            'model_field' => 'required|string|max:255',
            // ... more rules
        ];
    }

    protected function getModelClass(): string
    {
        return CustomModel::class;
    }
}
```

## Best Practices

### File Preparation
1. **Use templates** as a starting point
2. **Validate data** in spreadsheet software first
3. **Remove empty rows** to avoid processing errors
4. **Check encoding** - save as UTF-8 if possible
5. **Use consistent formatting** for dates and numbers

### Import Strategy
1. **Import in order**: Communities → Apartments → People → Water Meters
2. **Start small**: Test with a few rows first
3. **Check dependencies**: Ensure referenced entities exist
4. **Backup data** before large imports
5. **Monitor progress** for large files

### Error Resolution
1. **Download error reports** to identify issues
2. **Fix source data** and re-import
3. **Use batch imports** for partial fixes
4. **Validate relationships** between entities

## API Reference

### ImportManager
```php
$importManager = app(ImportManager::class);

// Import file
$stats = $importManager->import($type, $filePath, $options);

// Register custom importer
$importManager->registerImporter('custom', CustomImporter::class);

// Get available importers
$types = $importManager->getAvailableImporters();
```

### CsvTemplateGenerator
```php
$generator = app(CsvTemplateGenerator::class);

// Generate template
$csv = $generator->generateTemplate('communities', true);

// Download template
return $generator->downloadTemplate('apartments', false);

// Get template info
$info = $generator->getTemplateInfo('people');
```

### ImportMappingService
```php
$mappingService = app(ImportMappingService::class);

// Detect columns
$columns = $mappingService->detectColumns($filePath, $options);

// Suggest mappings
$mappings = $mappingService->suggestMappings($headers);
```

## Configuration

### Import Options
```php
$options = [
    'delimiter' => ',',           // CSV delimiter
    'encoding' => 'UTF-8',        // File encoding
    'batch_size' => 500,          // Batch processing size
    'skip_header' => true,        // Skip first row
    'community_id' => 1,          // For apartment imports
];
```

### Performance Tuning
- Increase `batch_size` for better performance on large files
- Use queue jobs for imports > 1000 rows
- Monitor memory usage during imports
- Consider database indexing for lookup operations

## Troubleshooting

### Common Issues

**"File not found" error**
- Check file path and permissions
- Ensure file is uploaded correctly

**"Validation failed" errors**
- Download error report for details
- Check required fields are present
- Verify data formats (dates, numbers)

**"Community not found" error (apartments/water meters)**
- Ensure community exists before importing apartments
- Check community name spelling in CSV

**Memory issues with large files**
- Reduce batch size
- Use queue processing
- Split large files into smaller chunks

**Encoding problems**
- Save CSV as UTF-8
- Use encoding option in import settings
- Check for special characters

### Performance Issues
- Large files (>10MB): Use command line import
- Many validation errors: Fix data before import
- Slow processing: Increase batch size or use queues

## Security Considerations

- Only administrators can perform imports
- Files are stored securely and cleaned up after processing
- Input validation prevents SQL injection
- Transaction rollback on errors prevents partial data corruption
- Audit trail through ImportJob model

## Monitoring and Logging

All import activities are logged and can be monitored through:
- ImportJob records in the database
- Laravel logs for detailed error information
- Filament admin panel for visual monitoring
- Email notifications for import completion/failure