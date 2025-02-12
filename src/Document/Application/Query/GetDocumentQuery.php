<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

class GetDocumentQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function query(QueryParameters $parameters): Document
    {
        assert($parameters instanceof GetDocument);

        $document = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->from('documents')
            ->where('id', '=', $parameters->id)
            ->fetchOne();

        return $this->denormalizer->denormalize($document, Document::class);
    }
}
