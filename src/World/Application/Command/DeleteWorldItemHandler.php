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
            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('world_item_relations')
                ->where('source_world_item_id', '=', $command->item->getId())
                ->orWhere([['target_world_item_id', '=', $command->item->getId()]])
                ->execute();

            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('world_item_images')
                ->where('world_item_id', '=', $command->item->getId())
                ->execute();

            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('world_item_documents')
                ->where('world_item_id', '=', $command->item->getId())
                ->execute();

            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('world_item_conversations')
                ->where('world_item_id', '=', $command->item->getId())
                ->execute();

            // DELETE the Item itself
            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('world_items')
                ->where('id', '=', $command->item->getId())
                ->execute();

            $this->databasePlatform->commit();
        } catch (Throwable $e) {
            $this->databasePlatform->rollback();

            throw $e;
        }

        return new MessageEventResult([new ItemDeleted($command->item)]);
    }
}
