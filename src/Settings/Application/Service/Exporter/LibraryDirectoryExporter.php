<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use Symfony\Component\Finder\Finder;
use ZipArchive;

final readonly class LibraryDirectoryExporter implements SingleExport
{
    public function __construct(
        private string $directoryStoragePath,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->directoryStoragePath)
            ->files();

        foreach ($finder as $directory) {
            $archive->addFile($directory->getRealPath(), 'library/directory/' . $directory->getFilename());
        }
    }
}
