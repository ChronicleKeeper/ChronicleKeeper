<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Event;

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
            ['pruner' => self::class, 'tables' => ['images', 'images_vectors']],
        );
        try {
            $this->connection->beginTransaction();

            $this->connection->executeStatement('DELETE FROM images_vectors');
            $this->connection->executeStatement('DELETE FROM images');

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
