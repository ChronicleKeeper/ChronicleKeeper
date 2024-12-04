<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class FindAllDocumentVectors implements QueryParameters
{
    public function getQueryClass(): string
    {
        return FindAllDocumentVectorsQuery::class;
    }
}
