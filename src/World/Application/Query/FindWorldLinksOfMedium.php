<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class FindWorldLinksOfMedium implements QueryParameters
{
    public function __construct(
        public readonly string $type,
        public readonly string $mediumId,
    ) {
    }

    public function getQueryClass(): string
    {
        return FindWorldLinksOfMediumQuery::class;
    }
}
