<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;

use function assert;

final class SearchWorldItemsCountQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function query(QueryParameters $parameters): int
    {
        assert($parameters instanceof SearchWorldItemsCount);

        $queryBuilder = $this->platform->createQueryBuilder()->createSelect()
            ->select('COUNT(id) as count')
            ->from('world_items');

        if ($parameters->search !== '') {
            $queryBuilder->where('name', 'LIKE', '%' . $parameters->search . '%');
        }

        if ($parameters->type !== '') {
            $queryBuilder->where('type', '=', $parameters->type);
        }

        if ($parameters->exclude !== []) {
            $queryBuilder->where('id', 'NOT IN', $parameters->exclude);
        }

        return $queryBuilder->fetchOne()['count'];
    }
}
