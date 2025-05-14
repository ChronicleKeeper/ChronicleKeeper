<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreItemRelationHandler
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(StoreItemRelation $itemRelation): void
    {
        // Check if relation already exists
        if ($this->relationExists($itemRelation)) {
            return;
        }

        // Insert the new relation
        $this->connection->createQueryBuilder()
            ->insert('world_item_relations')
            ->values([
                'source_world_item_id' => ':source_id',
                'target_world_item_id' => ':target_id',
                'relation_type' => ':relation_type',
            ])
            ->setParameter('source_id', $itemRelation->sourceItemId)
            ->setParameter('target_id', $itemRelation->targetItemId)
            ->setParameter('relation_type', $itemRelation->relationType)
            ->executeStatement();
    }

    /**
     * Check if a relation exists in either direction with the given type
     */
    private function relationExists(StoreItemRelation $itemRelation): bool
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expr         = $queryBuilder->expr();

        $result = $queryBuilder
            ->select('1')
            ->from('world_item_relations')
            ->where(
                $expr->or(
                    $expr->and(
                        $expr->eq('source_world_item_id', ':source_id'),
                        $expr->eq('target_world_item_id', ':target_id'),
                    ),
                    $expr->and(
                        $expr->eq('source_world_item_id', ':target_id'),
                        $expr->eq('target_world_item_id', ':source_id'),
                    ),
                ),
            )
            ->andWhere($expr->eq('relation_type', ':relation_type'))
            ->setParameter('source_id', $itemRelation->sourceItemId)
            ->setParameter('target_id', $itemRelation->targetItemId)
            ->setParameter('relation_type', $itemRelation->relationType)
            ->executeQuery()
            ->fetchOne();

        return $result !== false;
    }
}
