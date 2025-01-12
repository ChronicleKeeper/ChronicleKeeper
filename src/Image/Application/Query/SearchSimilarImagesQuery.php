<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;

use function assert;
use function implode;

class SearchSimilarImagesQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly QueryService $queryService,
    ) {
    }

    /** @return list<array{image: Image, content: string, distance: float}> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof SearchSimilarImages);

        $foundVectors = $this->databasePlatform->fetch(
            'SELECT image_id, distance, content FROM images_vectors WHERE embedding match :embedding AND k = 4096 AND distance < :maxDistance ORDER BY distance LIMIT :maxResults',
            [
                'embedding' => '[' . implode(',', $parameters->searchedVectors) . ']',
                'maxDistance' => $parameters->maxDistance,
                'maxResults' => $parameters->maxResults,
            ],
        );

        $results = [];
        foreach ($foundVectors as $vector) {
            $image = $this->queryService->query(new GetImage($vector['image_id']));

            $results[] = [
                'image' => $image,
                'content' => $vector['content'],
                'distance' => $vector['distance'],
            ];
        }

        return $results;
    }
}
