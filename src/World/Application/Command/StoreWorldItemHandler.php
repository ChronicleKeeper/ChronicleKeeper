<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use ChronicleKeeper\World\Domain\ValueObject\ConversationReference;
use ChronicleKeeper\World\Domain\ValueObject\DocumentReference;
use ChronicleKeeper\World\Domain\ValueObject\ImageReference;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class StoreWorldItemHandler
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(StoreWorldItem $command): MessageEventResult
    {
        try {
            $this->connection->beginTransaction();

            // Insert or update the world item
            $this->upsertWorldItem($command);

            // Process media references
            foreach ($command->item->getMediaReferences() as $media) {
                if ($media instanceof DocumentReference) {
                    $this->upsertItemMedia(
                        'world_item_documents',
                        $command->item->getId(),
                        $media->document->getId(),
                        'document_id',
                    );
                } elseif ($media instanceof ImageReference) {
                    $this->upsertItemMedia(
                        'world_item_images',
                        $command->item->getId(),
                        $media->image->getId(),
                        'image_id',
                    );
                } elseif ($media instanceof ConversationReference) {
                    $this->upsertItemMedia(
                        'world_item_conversations',
                        $command->item->getId(),
                        $media->conversation->getId(),
                        'conversation_id',
                    );
                }
            }

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();

            throw $e;
        }

        return new MessageEventResult($command->item->flushEvents());
    }

    /**
     * Insert or update a world item
     */
    private function upsertWorldItem(StoreWorldItem $command): void
    {
        $itemId = $command->item->getId();

        // Check if the item exists
        $exists = $this->connection->createQueryBuilder()
            ->select('1')
            ->from('world_items')
            ->where('id = :id')
            ->setParameter('id', $itemId)
            ->executeQuery()
            ->fetchOne();

        if ($exists !== false) {
            // Update existing item
            $this->connection->createQueryBuilder()
                ->update('world_items')
                ->set('type', ':type')
                ->set('name', ':name')
                ->set('short_description', ':short_description')
                ->where('id = :id')
                ->setParameter('id', $itemId)
                ->setParameter('type', $command->item->getType()->value)
                ->setParameter('name', $command->item->getName())
                ->setParameter('short_description', $command->item->getShortDescription())
                ->executeStatement();
        } else {
            // Insert new item
            $this->connection->createQueryBuilder()
                ->insert('world_items')
                ->values([
                    'id' => ':id',
                    'type' => ':type',
                    'name' => ':name',
                    'short_description' => ':short_description',
                ])
                ->setParameter('id', $itemId)
                ->setParameter('type', $command->item->getType()->value)
                ->setParameter('name', $command->item->getName())
                ->setParameter('short_description', $command->item->getShortDescription())
                ->executeStatement();
        }
    }

    /**
     * Insert or update a media reference for a world item
     */
    private function upsertItemMedia(string $table, string $itemId, string $mediaId, string $mediaIdColumn): void
    {
        // Check if the media reference exists
        $exists = $this->connection->createQueryBuilder()
            ->select('1')
            ->from($table)
            ->where('world_item_id = :itemId')
            ->andWhere($mediaIdColumn . ' = :mediaId')
            ->setParameter('itemId', $itemId)
            ->setParameter('mediaId', $mediaId)
            ->executeQuery()
            ->fetchOne();

        if ($exists !== false) {
            return;
        }

        // Insert only if it doesn't exist yet
        $this->connection->createQueryBuilder()
            ->insert($table)
            ->values([
                'world_item_id' => ':itemId',
                $mediaIdColumn => ':mediaId',
            ])
            ->setParameter('itemId', $itemId)
            ->setParameter('mediaId', $mediaId)
            ->executeStatement();
    }
}
