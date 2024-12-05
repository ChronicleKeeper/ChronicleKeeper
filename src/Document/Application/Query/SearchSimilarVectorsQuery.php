<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Document\Domain\Entity\SearchVector;
use ChronicleKeeper\Document\Domain\Entity\VectorDocument;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\Distance\CosineDistance;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;

use function array_keys;
use function array_slice;
use function asort;
use function assert;

class SearchSimilarVectorsQuery implements Query
{
    public function __construct(
        private readonly QueryService $queryService,
        private readonly CosineDistance $distance,
    ) {
    }

    /** @return list<array{vector: VectorDocument, distance: float}> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof SearchSimilarVectors);

        /** @var list<SearchVector> $searchVectors */
        $searchVectors = $this->queryService->query(new GetAllVectorSearchDocuments());

        $distances = [];
        foreach ($searchVectors as $index => $vector) {
            $dist = $this->distance->measure($parameters->searchedVectors, $vector->vectors);
            if ($dist > $parameters->maxDistance) {
                unset($searchVectors[$index]);
                continue;
            }

            $distances[$index] = $dist;
        }

        asort($distances);

        $topKIndices = array_slice(array_keys($distances), 0, $parameters->maxResults, true);

        $results = [];
        foreach ($topKIndices as $index) {
            $vectorDocument = $this->queryService->query(new GetVectorDocument($searchVectors[$index]->id));

            $results[] = [
                'vector' => $vectorDocument,
                'distance' => $distances[$index],
            ];
        }

        return $results;
    }
}
