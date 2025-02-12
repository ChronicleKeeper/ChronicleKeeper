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

        $fromItem = $parameters->itemid;

        $rawRelations = $this->platform->createQueryBuilder()->createSelect()
            ->select(
                'source_world_item_id as from_item',
                'target_world_item_id as to_item',
                'relation_type as relation_type',
            )
            ->from('world_item_relations')
            ->orWhere([['source_world_item_id', '=', $fromItem], ['target_world_item_id', '=', $fromItem]])
            ->fetchAll();

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
