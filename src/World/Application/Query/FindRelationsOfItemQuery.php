<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\Relation;
use Doctrine\DBAL\Connection;

use function assert;

class FindRelationsOfItemQuery implements Query
{
    public function __construct(
        private readonly Connection $connection,
        private readonly QueryService $queryService,
    ) {
    }

    /** @return Relation[] */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindRelationsOfItem);

        $fromItem = $parameters->itemid;

        $queryBuilder = $this->connection->createQueryBuilder();
        $expr         = $queryBuilder->expr();

        $rawRelations = $queryBuilder
            ->select(
                'source_world_item_id as from_item',
                'target_world_item_id as to_item',
                'relation_type',
            )
            ->from('world_item_relations')
            ->where(
                $expr->or(
                    $expr->eq('source_world_item_id', ':item_id'),
                    $expr->eq('target_world_item_id', ':item_id'),
                ),
            )
            ->setParameter('item_id', $fromItem)
            ->executeQuery()
            ->fetchAllAssociative();

        $relations = [];
        foreach ($rawRelations as $relation) {
            $relationTarget = $relation['from_item'] === $fromItem ? 'to_item' : 'from_item';
            $toItem         = $this->queryService->query(new GetWorldItem($relation[$relationTarget]));
            assert($toItem instanceof Item);

            $relations[] = new Relation($toItem, $relation['relation_type']);
        }

        return $relations;
    }
}
