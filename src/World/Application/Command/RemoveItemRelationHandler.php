<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveItemRelationHandler
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(RemoveItemRelation $itemRelation): void
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expr         = $queryBuilder->expr();

        $queryBuilder
            ->delete('world_item_relations')
            ->where(
                $expr->or(
                    $expr->and(
                        $expr->eq('source_world_item_id', ':source_world_item_id'),
                        $expr->eq('target_world_item_id', ':target_world_item_id'),
                    ),
                    $expr->and(
                        $expr->eq('source_world_item_id', ':target_world_item_id'),
                        $expr->eq('target_world_item_id', ':source_world_item_id'),
                    ),
                ),
            )
            ->andWhere($expr->eq('relation_type', ':relation_type'))
            ->setParameter('source_world_item_id', $itemRelation->sourceItemId)
            ->setParameter('target_world_item_id', $itemRelation->targetItemId)
            ->setParameter('relation_type', $itemRelation->relationType)
            ->executeStatement();
    }
}
