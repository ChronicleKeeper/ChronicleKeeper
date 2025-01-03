<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Document\Domain\Entity\VectorDocument;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;

use function array_filter;
use function array_values;
use function assert;

class FindVectorsOfDocumentQuery implements Query
{
    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    /** @return list<VectorDocument> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindVectorsOfDocument);

        /** @var VectorDocument[] $documents */
        $documents = $this->queryService->query(new FindAllDocumentVectors());

        return array_values(array_filter(
            $documents,
            static fn (VectorDocument $document) => $document->document->getId() === $parameters->id,
        ));
    }
}
