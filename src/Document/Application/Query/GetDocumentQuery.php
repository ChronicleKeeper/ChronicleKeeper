<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

class GetDocumentQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly Connection $connection,
    ) {
    }

    public function query(QueryParameters $parameters): Document
    {
        assert($parameters instanceof GetDocument);

        $document = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('documents')
            ->where('id = :id')
            ->setParameter('id', $parameters->id)
            ->executeQuery()
            ->fetchAssociative();

        return $this->denormalizer->denormalize($document, Document::class);
    }
}
