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
            $this->platform->query(
                'DELETE FROM world_item_documents WHERE world_item_id = :itemId AND document_id = :mediumId',
                [
                    'itemId' => $command->itemId,
                    'mediumId' => $command->mediumId,
                ],
            );

            return;
        }

        if ($command->mediumType === 'image') {
            $this->platform->query(
                'DELETE FROM world_item_images WHERE world_item_id = :itemId AND image_id = :mediumId',
                [
                    'itemId' => $command->itemId,
                    'mediumId' => $command->mediumId,
                ],
            );

            return;
        }

        if ($command->mediumType === 'conversation') {
            $this->platform->query(
                'DELETE FROM world_item_conversations WHERE world_item_id = :itemId AND conversation_id = :mediumId',
                [
                    'itemId' => $command->itemId,
                    'mediumId' => $command->mediumId,
                ],
            );

            return;
        }
    }
}
