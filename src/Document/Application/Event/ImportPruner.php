<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Event;

use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ExecuteImportPruning::class)]
class ImportPruner
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExecuteImportPruning $event): void
    {
        $this->logger->debug(
            'Import - Pruning documents and their vectors.',
            ['pruner' => self::class, 'tables' => ['documents', 'documents_vectors']],
        );

        $this->databasePlatform->truncateTable('documents_vectors');
        $this->databasePlatform->truncateTable('documents');
    }
}
