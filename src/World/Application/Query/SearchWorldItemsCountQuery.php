<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;

use function assert;

use function implode;

final class SearchWorldItemsCountQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function query(QueryParameters $parameters): int
    {
        assert($parameters instanceof SearchWorldItemsCount);

        $query           = 'SELECT COUNT(id) as count FROM world_items';
        $queryParameters = [];
        $addWhere        = [];

        if ($parameters->search !== '') {
            $addWhere[]                = 'name LIKE :search';
            $queryParameters['search'] = '%' . $parameters->search . '%';
        }

        if ($parameters->type !== '') {
            $addWhere[]              = 'type = :type';
            $queryParameters['type'] = $parameters->type;
        }

        if ($parameters->exclude !== []) {
            $addWhere[]                 = 'id NOT IN (:exclude)';
            $queryParameters['exclude'] = implode(',', $parameters->exclude);
        }

        if ($addWhere !== []) {
            $query .= ' WHERE ' . implode(' AND ', $addWhere);
        }

        return $this->platform->fetchSingleRow($query, $queryParameters)['count']; // @phpstan-ignore offsetAccess.notFound
    }
}
