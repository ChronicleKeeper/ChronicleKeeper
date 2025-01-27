<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Event;

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

        $this->databasePlatform->truncateTable('world_item_conversations');
        $this->databasePlatform->truncateTable('world_item_documents');
        $this->databasePlatform->truncateTable('world_item_images');
        $this->databasePlatform->truncateTable('world_item_relations');
        $this->databasePlatform->truncateTable('world_items');
    }
}
