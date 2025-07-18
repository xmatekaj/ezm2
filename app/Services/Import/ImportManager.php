<?php

namespace App\Services\Import;

use Exception;

class ImportManager
{
    protected array $importers = [
        'communities' => CommunityImporter::class,
        'apartments' => ApartmentImporter::class,
    ];

    public function getAvailableImporters(): array
    {
        return array_keys($this->importers);
    }

    public function import(string $type, string $filePath, array $options = []): array
    {
        if (!isset($this->importers[$type])) {
            throw new Exception("Unknown importer type: {$type}");
        }

        $importerClass = $this->importers[$type];
        $importer = new $importerClass();

        return $importer->import($filePath, $options);
    }

    public function registerImporter(string $type, string $importerClass): void
    {
        $this->importers[$type] = $importerClass;
    }

    protected function validateTypeSpecificRequirements(array $rows, string $type, array $options): array
{
    $issues = [];

    if (empty($rows)) {
        return ['valid' => false, 'issues' => [__('app.import.no_data_rows')]];
    }

    // Check for required columns based on import type
    $firstRow = $rows[0];
    $requiredColumns = $this->getRequiredColumns($type);

    foreach ($requiredColumns as $column) {
        if (!$this->hasColumn($firstRow, $column)) {
            $issues[] = __('app.import.missing_required_column', ['column' => __('app.import.columns.' . $column, [], $column)]);
        }
    }

    // Special validation for apartments
    if ($type === 'apartments' && !isset($options['community_id'])) {
        $issues[] = __('app.import.apartment.community_required');
    }

    return [
        'valid' => empty($issues),
        'issues' => $issues
    ];
}

}
