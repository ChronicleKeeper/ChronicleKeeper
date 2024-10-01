<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use Symfony\Component\Finder\Finder;
use ZipArchive;

final readonly class LibraryDocumentExporter implements SingleExport
{
    public function __construct(
        private string $documentStoragePath,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->documentStoragePath)
            ->files();

        foreach ($finder as $document) {
            $archive->addFile($document->getRealPath(), 'library/document/' . $document->getFilename());
        }
    }
}
