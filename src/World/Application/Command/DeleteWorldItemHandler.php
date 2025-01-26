<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use ChronicleKeeper\World\Domain\Event\ItemDeleted;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class DeleteWorldItemHandler
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function __invoke(DeleteWorldItem $command): MessageEventResult
    {
        try {
            $this->databasePlatform->beginTransaction();

            // Delete Relations, Images, Documents and Conversations
            $this->databasePlatform->query(
                'DELETE FROM world_item_relations WHERE source_world_item_id = :id OR target_world_item_id = :id',
                ['id' => $command->item->getId()],
            );

            $this->databasePlatform->query(
                'DELETE FROM world_item_images WHERE world_item_id = :id',
                ['id' => $command->item->getId()],
            );

            $this->databasePlatform->query(
                'DELETE FROM world_item_documents WHERE world_item_id = :id',
                ['id' => $command->item->getId()],
            );

            $this->databasePlatform->query(
                'DELETE FROM world_item_conversations WHERE world_item_id = :id',
                ['id' => $command->item->getId()],
            );

            // DELETE the Item itself
            $this->databasePlatform->query(
                'DELETE FROM world_items WHERE id = :id',
                ['id' => $command->item->getId()],
            );

            $this->databasePlatform->commit();
        } catch (Throwable $e) {
            $this->databasePlatform->rollback();

            throw $e;
        }

        return new MessageEventResult([new ItemDeleted($command->item)]);
    }
}
