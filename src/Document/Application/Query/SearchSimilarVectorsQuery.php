<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;

use function assert;
use function implode;

class SearchSimilarVectorsQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly QueryService $queryService,
    ) {
    }

    /** @return list<array{document: Document, content: string, distance: float}> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof SearchSimilarVectors);

        $foundVectors = $this->databasePlatform->fetch(
            'SELECT document_id, distance, content FROM documents_vectors WHERE embedding match :embedding AND k = 4096 AND distance < :maxDistance ORDER BY distance LIMIT :maxResults',
            [
                'embedding' => '[' . implode(',', $parameters->searchedVectors) . ']',
                'maxDistance' => $parameters->maxDistance,
                'maxResults' => $parameters->maxResults,
            ],
        );

        $results = [];
        foreach ($foundVectors as $vector) {
            $document = $this->queryService->query(new GetDocument($vector['document_id']));

            $results[] = [
                'document' => $document,
                'content' => $vector['content'],
                'distance' => $vector['distance'],
            ];
        }

        return $results;
    }
}
