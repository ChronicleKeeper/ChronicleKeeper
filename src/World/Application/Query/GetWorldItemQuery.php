<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\World\Domain\Entity\Item;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

class GetWorldItemQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function query(QueryParameters $parameters): Item
    {
        assert($parameters instanceof GetWorldItem);

        $document = $this->databasePlatform->fetchSingleRow(
            'SELECT id, type, name, short_description as shortDescription FROM world_items WHERE id = :id',
            ['id' => $parameters->id],
        );

        return $this->denormalizer->denormalize($document, Item::class);
    }
}
