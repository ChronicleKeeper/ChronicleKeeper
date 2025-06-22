<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use ChronicleKeeper\World\Domain\Event\ItemDeleted;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class DeleteWorldItemHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(DeleteWorldItem $command): MessageEventResult
    {
        try {
            $this->connection->beginTransaction();

            // Delete Relations
            $this->deleteItemRelations($command->item->getId());

            // Delete Images
            $this->deleteItemAssociations(
                'world_item_images',
                'world_item_id',
                $command->item->getId(),
            );

            // Delete Documents
            $this->deleteItemAssociations(
                'world_item_documents',
                'world_item_id',
                $command->item->getId(),
            );

            // Delete Conversations
            $this->deleteItemAssociations(
                'world_item_conversations',
                'world_item_id',
                $command->item->getId(),
            );

            // Delete the Item itself
            $this->connection->createQueryBuilder()
                ->delete('world_items')
                ->where('id = :id')
                ->setParameter('id', $command->item->getId())
                ->executeStatement();

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();

            throw $e;
        }

        return new MessageEventResult([new ItemDeleted($command->item)]);
    }

    /**
     * Delete relations where the item appears as source or target
     */
    private function deleteItemRelations(string $itemId): void
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expr         = $queryBuilder->expr();

        $queryBuilder
            ->delete('world_item_relations')
            ->where($expr->eq('source_world_item_id', ':itemId'))
            ->orWhere($expr->eq('target_world_item_id', ':itemId'))
            ->setParameter('itemId', $itemId)
            ->executeStatement();
    }

    /**
     * Delete item associations from a specific table
     */
    private function deleteItemAssociations(string $table, string $columnName, string $itemId): void
    {
        $this->connection->createQueryBuilder()
            ->delete($table)
            ->where($columnName . ' = :itemId')
            ->setParameter('itemId', $itemId)
            ->executeStatement();
    }
}
