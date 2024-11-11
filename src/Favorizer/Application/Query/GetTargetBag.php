<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class GetTargetBag implements QueryParameters
{
    public function getQueryClass(): string
    {
        return GetTargetBagQuery::class;
    }
}
