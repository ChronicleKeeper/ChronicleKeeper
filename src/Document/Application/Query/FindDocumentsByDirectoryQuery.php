<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function array_map;
use function assert;

class FindDocumentsByDirectoryQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly Connection $connection,
    ) {
    }

    /** @return array<int, Document> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindDocumentsByDirectory);

        $documents = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('documents')
            ->where('directory = :directoryId')
            ->setParameter('directoryId', $parameters->id)
            ->orderBy('title')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(
            fn (array $document) => $this->denormalizer->denormalize($document, Document::class),
            $documents,
        );
    }
}
