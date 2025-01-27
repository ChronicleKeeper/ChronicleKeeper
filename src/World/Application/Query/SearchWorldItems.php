<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class SearchWorldItems implements QueryParameters
{
    /** @param string[] $exclude */
    public function __construct(
        public readonly string $search = '',
        public readonly string $type = '',
        public readonly array $exclude = [],
        public int $offset = 0,
        public int $limit = 10,
    ) {
    }

    public function getQueryClass(): string
    {
        return SearchWorldItemsQuery::class;
    }
}
