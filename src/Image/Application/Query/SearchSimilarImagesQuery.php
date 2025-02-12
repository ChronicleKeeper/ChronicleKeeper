<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;

use function assert;

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

        $foundVectors = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->select('image_id', 'content')
            ->from('images_vectors')
            ->withVectorSearch(
                'embedding',
                $parameters->searchedVectors,
                'distance',
                $parameters->maxDistance,
            )
            ->limit($parameters->maxResults)
            ->orderBy('distance')
            ->fetchAll();

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
