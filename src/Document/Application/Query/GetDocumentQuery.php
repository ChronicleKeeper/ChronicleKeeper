<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

class GetDocumentQuery implements Query
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function query(QueryParameters $parameters): Document
    {
        assert($parameters instanceof GetDocument);

        return $this->serializer->deserialize(
            $this->fileAccess->read('library.documents', $parameters->id . '.json'),
            Document::class,
            'json',
        );
    }
}
