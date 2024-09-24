<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use Symfony\Component\Finder\Finder;
use ZipArchive;

final class VectorStorageDocumentsExporter implements SingleExport
{
    public function __construct(
        private readonly string $vectorDocumentsPath,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->vectorDocumentsPath)
            ->files();

        foreach ($finder as $vectorDocument) {
            $archive->addFile($vectorDocument->getRealPath(), 'vector/document/' . $vectorDocument->getFilename());
        }
    }
}
