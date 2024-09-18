<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Exporter;

use Symfony\Component\Finder\Finder;
use ZipArchive;

final class LibraryImagesExporter implements SingleExport
{
    public function __construct(
        private readonly string $libraryImageStoragePath,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->libraryImageStoragePath)
            ->files();

        foreach ($finder as $image) {
            $archive->addFile($image->getRealPath(), 'library/images/' . $image->getFilename());
        }
    }
}
