<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Webmozart\Assert\Assert;

class FindRelationsOfItem implements QueryParameters
{
    public function __construct(
        public readonly string $itemid,
    ) {
        Assert::uuid($this->itemid, 'Item id must be a valid UUID.');
    }

    public function getQueryClass(): string
    {
        return FindRelationsOfItemQuery::class;
    }
}
