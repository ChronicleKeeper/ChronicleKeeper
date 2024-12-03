<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class FindVectorsOfDocument implements QueryParameters
{
    public function __construct(
        public readonly string $id,
    ) {
    }

    public function getQueryClass(): string
    {
        return FindVectorsOfDocumentQuery::class;
    }
}
