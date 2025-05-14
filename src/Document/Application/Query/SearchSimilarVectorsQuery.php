<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

use function assert;
use function implode;

class SearchSimilarVectorsQuery implements Query
{
    public function __construct(
        private readonly Connection $connection,
        private readonly QueryService $queryService,
    ) {
    }

    /** @return list<array{document: Document, content: string, distance: float}> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof SearchSimilarVectors);

        $vectorString = '[' . implode(',', $parameters->searchedVectors) . ']';

        $sql = 'SELECT document_id, content,
                   (embedding <-> :vector) as distance
                FROM documents_vectors
                WHERE (embedding <-> :vector) < :maxDistance
                ORDER BY distance
                LIMIT :maxResults';

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('vector', $vectorString);
        $stmt->bindValue('maxDistance', $parameters->maxDistance);
        $stmt->bindValue('maxResults', $parameters->maxResults, ParameterType::INTEGER);

        $foundVectors = $stmt->executeQuery()->fetchAllAssociative();

        $results = [];
        foreach ($foundVectors as $vector) {
            $document = $this->queryService->query(new GetDocument($vector['document_id']));

            $results[] = [
                'document' => $document,
                'content' => $vector['content'],
                'distance' => (float) $vector['distance'],
            ];
        }

        return $results;
    }
}
