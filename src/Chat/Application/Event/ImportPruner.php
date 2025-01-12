<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Event;

use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[AsEventListener(event: ExecuteImportPruning::class)]
class ImportPruner
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly LoggerInterface $logger,
        private readonly PathRegistry $pathRegistry,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function __invoke(ExecuteImportPruning $event): void
    {
        if ($event->importSettings->pruneLibrary === false) {
            $this->logger->debug('Import - Skipping pruning of conversations.', ['pruner' => self::class]);

            return;
        }

        $this->logger->debug(
            'Import - Pruning conversations.',
            [
                'pruner' => self::class,
                'tables' => ['conversations', 'conversation_messages', 'conversation_settings'],
            ],
        );

        $this->databasePlatform->truncateTable('conversation_settings');
        $this->databasePlatform->truncateTable('conversation_messages');
        $this->databasePlatform->truncateTable('conversations');

        $this->pruneDirectoryContent($this->pathRegistry->get('temp'));
    }

    private function pruneDirectoryContent(string $path): void
    {
        $finder = (new Finder())
            ->in($path)
            ->ignoreDotFiles(true)
            ->files();

        foreach ($finder as $file) {
            $filePath = $file->getRealPath();

            $this->logger->debug('Import - Removing file.', ['file' => $filePath]);

            $this->filesystem->remove($filePath);
        }
    }
}
