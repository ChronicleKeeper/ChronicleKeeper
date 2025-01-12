<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class SearchSimilarImages implements QueryParameters
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
        return SearchSimilarImagesQuery::class;
    }
}
