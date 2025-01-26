<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class SearchWorldItems implements QueryParameters
{
    public function __construct(
        public readonly string $search = '',
        public readonly string $type = '',
        public readonly array $exclude = [],
    ) {
    }

    public function getQueryClass(): string
    {
        return SearchWorldItemsQuery::class;
    }
}
