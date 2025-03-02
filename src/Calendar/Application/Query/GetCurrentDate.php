<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class GetCurrentDate implements QueryParameters
{
    public function getQueryClass(): string
    {
        return GetCurrentDateQuery::class;
    }
}
