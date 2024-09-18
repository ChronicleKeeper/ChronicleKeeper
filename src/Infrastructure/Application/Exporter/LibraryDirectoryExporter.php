<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Exporter;

use Symfony\Component\Finder\Finder;
use ZipArchive;

final class LibraryDirectoryExporter implements SingleExport
{
    public function __construct(
        private readonly string $directoryStoragePath,
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