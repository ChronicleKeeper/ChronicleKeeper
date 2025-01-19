<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class SearchWorldItems implements QueryParameters
{
    public function getQueryClass(): string
    {
        return SearchWorldItemsQuery::class;
    }
}
