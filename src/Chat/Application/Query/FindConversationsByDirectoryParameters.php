<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class FindConversationsByDirectoryParameters implements QueryParameters
{
    public function __construct(
        public readonly Directory $directory,
    ) {
    }

    public function getQueryClass(): string
    {
        return FindConversationsByDirectoryQuery::class;
    }
}
