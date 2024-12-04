<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class GetAllVectorSearchDocuments implements QueryParameters
{
    public function getQueryClass(): string
    {
        return GetAllVectorSearchDocumentsQuery::class;
    }
}
