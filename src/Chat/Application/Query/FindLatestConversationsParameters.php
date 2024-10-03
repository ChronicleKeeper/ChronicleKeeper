<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class FindLatestConversationsParameters implements QueryParameters
{
    public function __construct(
        public readonly int $maxEntries,
    ) {
    }

    public function getQueryClass(): string
    {
        return FindLatestConversationsQuery::class;
    }
}
