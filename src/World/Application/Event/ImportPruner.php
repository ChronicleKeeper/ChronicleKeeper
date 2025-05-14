<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Event;

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
            'Import - Pruning the world database.',
            [
                'pruner' => self::class,
                'tables' => [
                    'world_item_conversations',
                    'world_item_documents',
                    'world_item_images',
                    'world_item_relations',
                    'world_items',
                ],
            ],
        );
        try {
            $this->connection->beginTransaction();

            $this->connection->executeStatement('DELETE FROM world_item_conversations');
            $this->connection->executeStatement('DELETE FROM world_item_documents');
            $this->connection->executeStatement('DELETE FROM world_item_images');
            $this->connection->executeStatement('DELETE FROM world_item_relations');
            $this->connection->executeStatement('DELETE FROM world_items');

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();
            $this->logger->error(
                'Import - Pruning the world database failed.',
                [
                    'pruner' => self::class,
                    'error' => $e->getMessage(),
                ],
            );

            throw $e;
        }
    }
}
