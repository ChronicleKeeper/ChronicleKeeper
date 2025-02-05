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

        $foundVectors = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->select('document_id', 'distance', 'content')
            ->from('documents_vectors')
            ->where('embedding', 'match', '[' . implode(',', $parameters->searchedVectors) . ']')
            ->where('k', '=', $parameters->maxResults)
            ->where('distance', '<', $parameters->maxDistance)
            ->orderBy('distance')
            ->fetchAll();

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
