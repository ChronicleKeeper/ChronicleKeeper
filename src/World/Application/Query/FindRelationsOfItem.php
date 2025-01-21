<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\World\Domain\Entity\Item;

class FindRelationsOfItem implements QueryParameters
{
    public function __construct(
        public readonly Item $item,
    ) {
    }

    public function getQueryClass(): string
    {
        return FindRelationsOfItemQuery::class;
    }
}
