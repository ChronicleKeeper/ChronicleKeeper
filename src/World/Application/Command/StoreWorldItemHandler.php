<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\World\Domain\ValueObject\ConversationReference;
use ChronicleKeeper\World\Domain\ValueObject\DocumentReference;
use ChronicleKeeper\World\Domain\ValueObject\ImageReference;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class StoreWorldItemHandler
{
    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function __invoke(StoreWorldItem $command): void
    {
        try {
            $this->platform->beginTransaction();

            $this->platform->insertOrUpdate(
                'world_items',
                [
                    'id' => $command->item->getId(),
                    'type' => $command->item->getType()->value,
                    'name' => $command->item->getName(),
                    'short_description' => $command->item->getShortDescription(),
                ],
            );

            foreach ($command->item->getMediaReferences() as $media) {
                if ($media instanceof DocumentReference) {
                    $this->platform->insertOrUpdate(
                        'world_item_documents',
                        ['world_item_id' => $command->item->getId(), 'document_id' => $media->document->getId()],
                    );

                    continue;
                }

                if ($media instanceof ImageReference) {
                    $this->platform->insertOrUpdate(
                        'world_item_images',
                        ['world_item_id' => $command->item->getId(), 'image_id' => $media->image->getId()],
                    );

                    continue;
                }

                if (! ($media instanceof ConversationReference)) {
                    continue;
                }

                $this->platform->insertOrUpdate(
                    'world_item_conversations',
                    ['world_item_id' => $command->item->getId(), 'conversation_id' => $media->conversation->getId()],
                );
            }

            $this->platform->commit();
        } catch (Throwable $e) {
            $this->platform->rollback();

            throw $e;
        }
    }
}
