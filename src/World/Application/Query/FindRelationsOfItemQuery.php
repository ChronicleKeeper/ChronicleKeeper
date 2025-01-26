<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\Relation;

use function assert;

class FindRelationsOfItemQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $platform,
        private readonly QueryService $queryService,
    ) {
    }

    /** @return Relation[] */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindRelationsOfItem);

        $fromItem     = $parameters->itemid;
        $rawRelations = $this->platform->fetch(
            'SELECT source_world_item_id as fromItem, target_world_item_id as toItem, relation_type as relationType FROM world_item_relations WHERE source_world_item_id = :itemId OR target_world_item_id = :itemId',
            ['itemId' => $fromItem],
        );

        $relations = [];
        foreach ($rawRelations as $relation) {
            $relationTarget = $relation['fromItem'] === $fromItem ? 'toItem' : 'fromItem';
            $toItem         = $this->queryService->query(new GetWorldItem($relation[$relationTarget]));
            assert($toItem instanceof Item);

            $relations[] = new Relation($toItem, $relation['relationType']);
        }

        return $relations;
    }
}
