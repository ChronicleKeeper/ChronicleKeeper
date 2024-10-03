<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class FindConversationByIdParameters implements QueryParameters
{
    public function __construct(
        public readonly string $id,
    ) {
    }

    public function getQueryClass(): string
    {
        return FindConversationByIdQuery::class;
    }
}
