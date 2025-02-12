<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Event;

use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\DatabaseQueryException;
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
            $this->databasePlatform->beginTransaction();

            $this->databasePlatform->createQueryBuilder()->createDelete()->from('world_item_conversations')->execute();
            $this->databasePlatform->createQueryBuilder()->createDelete()->from('world_item_documents')->execute();
            $this->databasePlatform->createQueryBuilder()->createDelete()->from('world_item_images')->execute();
            $this->databasePlatform->createQueryBuilder()->createDelete()->from('world_item_relations')->execute();
            $this->databasePlatform->createQueryBuilder()->createDelete()->from('world_items')->execute();

            $this->databasePlatform->commit();
        } catch (DatabaseQueryException $e) {
            $this->databasePlatform->rollback();
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
