<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Doctrine\DBAL\Connection;

use function assert;
use function implode;

class SearchSimilarImagesQuery implements Query
{
    public function __construct(
        private readonly Connection $connection,
        private readonly QueryService $queryService,
    ) {
    }

    /** @return list<array{image: Image, content: string, distance: float}> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof SearchSimilarImages);

        // Using PostgreSQL vector search with pgvector extension
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('iv.image_id', 'iv.content', 'iv.embedding <=> :searchVector as distance')
            ->from('images_vectors', 'iv')
            ->where('iv.embedding <=> :searchVector <= :maxDistance')
            ->setParameter('searchVector', '[' . implode(',', $parameters->searchedVectors) . ']')
            ->setParameter('maxDistance', $parameters->maxDistance)
            ->orderBy('distance')
            ->setMaxResults($parameters->maxResults);

        $foundVectors = $queryBuilder->executeQuery()->fetchAllAssociative();

        $results = [];
        foreach ($foundVectors as $vector) {
            $image = $this->queryService->query(new GetImage($vector['image_id']));

            $results[] = [
                'image' => $image,
                'content' => $vector['content'],
                'distance' => (float) $vector['distance'],
            ];
        }

        return $results;
    }
}
