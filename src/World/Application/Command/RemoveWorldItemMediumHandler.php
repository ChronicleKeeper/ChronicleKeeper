<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveWorldItemMediumHandler
{
    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function __invoke(RemoveWorldItemMedium $command): void
    {
        if ($command->mediumType === 'document') {
            $this->platform->createQueryBuilder()->createDelete()
                ->from('world_item_documents')
                ->where('world_item_id', '=', $command->itemId)
                ->where('document_id', '=', $command->mediumId)
                ->execute();

            return;
        }

        if ($command->mediumType === 'image') {
            $this->platform->createQueryBuilder()->createDelete()
                ->from('world_item_images')
                ->where('world_item_id', '=', $command->itemId)
                ->where('image_id', '=', $command->mediumId)
                ->execute();

            return;
        }

        if ($command->mediumType === 'conversation') {
            $this->platform->createQueryBuilder()->createDelete()
                ->from('world_item_conversations')
                ->where('world_item_id', '=', $command->itemId)
                ->where('conversation_id', '=', $command->mediumId)
                ->execute();

            return;
        }
    }
}
