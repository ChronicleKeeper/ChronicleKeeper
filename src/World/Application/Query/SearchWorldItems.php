<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class SearchWorldItems implements QueryParameters
{
    /** @param list<string> $exclude */
    public function __construct(
        public readonly string $search = '',
        public readonly array $exclude = [],
    ) {
    }

    public function getQueryClass(): string
    {
        return SearchWorldItemsQuery::class;
    }
}
