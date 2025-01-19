<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class FindAllReferencableMedia implements QueryParameters
{
    public function __construct(
        public readonly string $search = '',
    ) {
    }

    public function getQueryClass(): string
    {
        return FindAllReferencableMediaQuery::class;
    }
}
