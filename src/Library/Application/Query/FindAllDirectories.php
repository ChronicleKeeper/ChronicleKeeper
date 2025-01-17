<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class FindAllDirectories implements QueryParameters
{
    public function getQueryClass(): string
    {
        return FindAllDirectoriesQuery::class;
    }
}
