<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Event;

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
            'Import - Pruning directories.',
            ['pruner' => self::class, 'tables' => ['directories']],
        );
        try {
            $this->connection->beginTransaction();

            $this->connection->executeStatement('DELETE FROM directories');

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
