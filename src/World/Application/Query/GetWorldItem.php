<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class GetWorldItem implements QueryParameters
{
    public function __construct(
        public readonly string $id,
    ) {
    }

    public function getQueryClass(): string
    {
        return GetWorldItemQuery::class;
    }
}
