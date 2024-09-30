<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class LibraryPruner
{
    public function __construct(
        public readonly Filesystem $filesystem,
        public readonly LoggerInterface $logger,
        public readonly string $directoryStoragePath,
        public readonly string $documentStoragePath,
        public readonly string $libraryImageStoragePath,
        public readonly string $vectorDocumentsPath,
        public readonly string $vectorImagesPath,
        public readonly string $conversationStoragePath,
    ) {
    }

    public function prune(): void
    {
        $this->pruneDirectoryContent($this->directoryStoragePath);
        $this->pruneDirectoryContent($this->documentStoragePath);
        $this->pruneDirectoryContent($this->libraryImageStoragePath);
        $this->pruneDirectoryContent($this->vectorDocumentsPath);
        $this->pruneDirectoryContent($this->vectorImagesPath);
        $this->pruneDirectoryContent($this->conversationStoragePath);
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
