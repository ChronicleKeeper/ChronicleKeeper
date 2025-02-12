<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Event;

use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Filesystem\Filesystem;

use const DIRECTORY_SEPARATOR;

#[AsEventListener(event: ExecuteImportPruning::class)]
class ImportPruner
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
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

        $this->databasePlatform->createQueryBuilder()->createDelete()->from('conversation_settings')->execute();
        $this->databasePlatform->createQueryBuilder()->createDelete()->from('conversation_messages')->execute();
        $this->databasePlatform->createQueryBuilder()->createDelete()->from('conversations')->execute();

        $temporaryConversationFile = $this->pathRegistry->get('temp')
            . DIRECTORY_SEPARATOR
            . 'conversation_temporary.json';

        $this->filesystem->remove($temporaryConversationFile);
    }
}
