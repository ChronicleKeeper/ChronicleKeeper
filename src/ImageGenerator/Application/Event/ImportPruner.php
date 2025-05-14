<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Event;

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
            'Import - Pruning image generations.',
            ['pruner' => self::class, 'tables' => ['generator_requests', 'generator_results']],
        );
        try {
            $this->connection->beginTransaction();

            $this->connection->executeStatement('DELETE FROM generator_results');
            $this->connection->executeStatement('DELETE FROM generator_requests');

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();
            $this->logger->error(
                'Import - Pruning failed.',
                ['pruner' => self::class, 'error' => $e->getMessage()],
            );

            throw $e;
        }
    }
}
