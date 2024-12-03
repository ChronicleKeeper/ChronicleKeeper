<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Library\Infrastructure\VectorStorage\Distance\CosineDistance;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorDocument;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Component\DependencyInjection\Attribute\Lazy;

use function array_keys;
use function array_slice;
use function asort;
use function assert;

#[Lazy]
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

        /** @var VectorDocument[] $vectorDocuments */
        $vectorDocuments = $this->queryService->query(new FindAllDocumentVectors());

        $distances = [];
        foreach ($vectorDocuments as $index => $document) {
            if ($document->document->content === '') {
                continue;
            }

            $dist = $this->distance->measure($parameters->searchedVectors, $document->vector);
            if ($dist > $parameters->maxDistance) {
                unset($vectorDocuments[$index]);
                continue;
            }

            $distances[$index] = $dist;
        }

        asort($distances);

        $topKIndices = array_slice(array_keys($distances), 0, $parameters->maxResults, true);

        $results = [];
        foreach ($topKIndices as $index) {
            $results[] = [
                'vector' => $vectorDocuments[$index],
                'distance' => $distances[$index],
            ];
        }

        return $results;
    }
}
