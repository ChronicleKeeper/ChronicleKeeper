<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Symfony\Component\Finder\Finder;
use ZipArchive;

final readonly class VectorStorageImagesExporter implements SingleExport
{
    public function __construct(
        private PathRegistry $pathRegistry,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->pathRegistry->get('vector.images'))
            ->files();

        foreach ($finder as $vectorImage) {
            $archive->addFile($vectorImage->getRealPath(), 'vector/image/' . $vectorImage->getFilename());
        }
    }
}
