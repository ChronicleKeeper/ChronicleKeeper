<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Event;

use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Filesystem\Filesystem;

use const DIRECTORY_SEPARATOR;

#[AsEventListener(event: ExecuteImportPruning::class)]
class ImportPruner
{
    public function __construct(
        private readonly Connection $connection,
        private readonly PathRegistry $pathRegistry,
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExecuteImportPruning $event): void
    {
        $this->logger->debug(
            'Import - Pruning conversations.',
            [
                'pruner' => self::class,
                'tables' => ['conversations', 'conversation_messages', 'conversation_settings'],
            ],
        );

        // Delete in reverse order to avoid foreign key constraints
        $this->connection->executeStatement('DELETE FROM conversation_settings');
        $this->connection->executeStatement('DELETE FROM conversation_messages');
        $this->connection->executeStatement('DELETE FROM conversations');

        $temporaryConversationFile = $this->pathRegistry->get('temp')
            . DIRECTORY_SEPARATOR
            . 'conversation_temporary.json';

        $this->filesystem->remove($temporaryConversationFile);
    }
}
