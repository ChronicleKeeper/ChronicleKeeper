<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final readonly class SearchWorldItemsCount implements QueryParameters
{
    /** @param string[] $exclude */
    public function __construct(
        public string $search = '',
        public string $type = '',
        public array $exclude = [],
    ) {
    }

    public function getQueryClass(): string
    {
        return SearchWorldItemsCountQuery::class;
    }
}
