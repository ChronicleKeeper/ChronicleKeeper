<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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
        $this->pruneDirectoryContent($this->pathRegistry->get('app.library.directories_storage'));
        $this->pruneDirectoryContent($this->pathRegistry->get('app.library.documents_storage'));
        $this->pruneDirectoryContent($this->pathRegistry->get('app.library.images_storage'));
        $this->pruneDirectoryContent($this->pathRegistry->get('app.vector.documents_storage'));
        $this->pruneDirectoryContent($this->pathRegistry->get('app.vector.images_storage'));
        $this->pruneDirectoryContent($this->pathRegistry->get('app.library.conversations_storage'));
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
}
