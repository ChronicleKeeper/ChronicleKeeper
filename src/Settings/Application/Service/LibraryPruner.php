<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use const DIRECTORY_SEPARATOR;

class LibraryPruner
{
    public function __construct(
        public readonly Filesystem $filesystem,
        public readonly LoggerInterface $logger,
        public readonly PathRegistry $pathRegistry,
    ) {
    }

    public function prune(): void
    {
        $this->pruneDirectoryContent($this->pathRegistry->get('library.directories'));
        $this->pruneDirectoryContent($this->pathRegistry->get('library.documents'));
        $this->pruneDirectoryContent($this->pathRegistry->get('library.images'));
        $this->pruneDirectoryContent($this->pathRegistry->get('vector.documents'));
        $this->pruneDirectoryContent($this->pathRegistry->get('vector.images'));
        $this->pruneDirectoryContent($this->pathRegistry->get('library.conversations'));

        $this->pruneDirectoryContent($this->pathRegistry->get('generator.requests'));
        $this->pruneDirectoryContent($this->pathRegistry->get('generator.images'));

        $this->pruneDirectoryContent($this->pathRegistry->get('temp'));

        $this->pruneFileFromPath($this->pathRegistry->get('storage'), 'favorites.json');
    }

    private function pruneDirectoryContent(string $path): void
    {
        $finder = (new Finder())
            ->in($path)
            ->ignoreDotFiles(true)
            ->files();

        foreach ($finder as $file) {
            $filePath = $file->getRealPath();

            $this->filesystem->remove($filePath);
            $this->logger->debug('Pruned file "' . $filePath . '"');
        }
    }

    private function pruneFileFromPath(string $path, string $fileName): void
    {
        $this->filesystem->remove($path . DIRECTORY_SEPARATOR . $fileName);
    }
}
