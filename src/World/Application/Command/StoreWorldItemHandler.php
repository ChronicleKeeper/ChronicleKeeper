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

            $this->platform->createQueryBuilder()->createInsert()
                ->asReplace()
                ->insert('world_items')
                ->values([
                    'id' => $command->item->getId(),
                    'type' => $command->item->getType()->value,
                    'name' => $command->item->getName(),
                    'short_description' => $command->item->getShortDescription(),
                ])
                ->execute();

            foreach ($command->item->getMediaReferences() as $media) {
                if ($media instanceof DocumentReference) {
                    $this->platform->createQueryBuilder()->createInsert()
                        ->asReplace()
                        ->insert('world_item_documents')
                        ->onConflict(['world_item_id', 'document_id'])
                        ->values([
                            'world_item_id' => $command->item->getId(),
                            'document_id' => $media->document->getId(),
                        ])
                        ->execute();

                    continue;
                }

                if ($media instanceof ImageReference) {
                    $this->platform->createQueryBuilder()->createInsert()
                        ->asReplace()
                        ->insert('world_item_images')
                        ->onConflict(['world_item_id', 'image_id'])
                        ->values([
                            'world_item_id' => $command->item->getId(),
                            'image_id' => $media->image->getId(),
                        ])
                        ->execute();

                    continue;
                }

                if (! ($media instanceof ConversationReference)) {
                    continue;
                }

                $this->platform->createQueryBuilder()->createInsert()
                    ->asReplace()
                    ->insert('world_item_conversations')
                    ->onConflict(['world_item_id', 'conversation_id'])
                    ->values([
                        'world_item_id' => $command->item->getId(),
                        'conversation_id' => $media->conversation->getId(),
                    ])
                    ->execute();
            }

            $this->platform->commit();
        } catch (Throwable $e) {
            $this->platform->rollback();

            throw $e;
        }
    }
}
