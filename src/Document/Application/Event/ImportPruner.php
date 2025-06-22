<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Event;

use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ExecuteImportPruning::class)]
final class ImportPruner
{
    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(): void
    {
        $this->logger->debug(
            'Import - Pruning documents and their vectors.',
            ['pruner' => self::class, 'tables' => ['documents', 'documents_vectors']],
        );
        try {
            $this->connection->beginTransaction();

            // Delete in reverse order to respect potential foreign key constraints
            $this->connection->executeStatement('DELETE FROM documents_vectors');
            $this->connection->executeStatement('DELETE FROM documents');

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }
}
