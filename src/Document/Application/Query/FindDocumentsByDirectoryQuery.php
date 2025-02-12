<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function array_map;
use function assert;

class FindDocumentsByDirectoryQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    /** @return array<int, Document> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindDocumentsByDirectory);

        $documents = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->from('documents')
            ->where('directory', '=', $parameters->id)
            ->orderBy('title')
            ->fetchAll();

        return array_map(
            fn (array $document) => $this->denormalizer->denormalize($document, Document::class),
            $documents,
        );
    }
}
