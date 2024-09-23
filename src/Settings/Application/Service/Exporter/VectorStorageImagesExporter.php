<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Application\Service\Exporter;

use Symfony\Component\Finder\Finder;
use ZipArchive;

final class VectorStorageImagesExporter implements SingleExport
{
    public function __construct(
        private readonly string $vectorImagesPath,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->vectorImagesPath)
            ->files();

        foreach ($finder as $vectorImage) {
            $archive->addFile($vectorImage->getRealPath(), 'vector/image/' . $vectorImage->getFilename());
        }
    }
}
