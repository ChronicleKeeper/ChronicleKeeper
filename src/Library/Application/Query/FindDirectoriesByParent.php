<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Webmozart\Assert\Assert;

final class FindDirectoriesByParent implements QueryParameters
{
    public function __construct(
        public readonly string $parentId,
    ) {
        Assert::uuid($parentId);
    }

    public function getQueryClass(): string
    {
        return FindDirectoriesByParentQuery::class;
    }
}
