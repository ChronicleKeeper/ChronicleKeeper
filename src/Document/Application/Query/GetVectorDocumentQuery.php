<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Document\Domain\Entity\VectorDocument;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

class GetVectorDocumentQuery implements Query
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function query(QueryParameters $parameters): VectorDocument
    {
        assert($parameters instanceof GetVectorDocument);

        return $this->serializer->deserialize(
            $this->fileAccess->read('vector.documents', $parameters->id . '.json'),
            VectorDocument::class,
            'json',
        );
    }
}
