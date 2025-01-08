<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class FindImagesByDirectory implements QueryParameters
{
    public function __construct(
        public readonly string $id,
    ) {
    }

    public function getQueryClass(): string
    {
        return FindImagesByDirectoryQuery::class;
    }
}
