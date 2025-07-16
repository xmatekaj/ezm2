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
}
