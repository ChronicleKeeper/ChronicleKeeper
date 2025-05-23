<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class GetVectorImage implements QueryParameters
{
    public function __construct(
        public readonly string $id,
    ) {
    }

    public function getQueryClass(): string
    {
        return GetVectorImageQuery::class;
    }
}
