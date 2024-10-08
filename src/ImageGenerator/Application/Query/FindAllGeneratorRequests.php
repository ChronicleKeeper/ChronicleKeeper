<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class FindAllGeneratorRequests implements QueryParameters
{
    public function getQueryClass(): string
    {
        return FindAllGeneratorRequestsQuery::class;
    }
}
