<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class SearchSimilarVectors implements QueryParameters
{
    /** @param list<float> $searchedVectors */
    public function __construct(
        public readonly array $searchedVectors,
        public readonly float $maxDistance,
        public readonly int $maxResults,
    ) {
    }

    public function getQueryClass(): string
    {
        return SearchSimilarVectorsQuery::class;
    }
}
