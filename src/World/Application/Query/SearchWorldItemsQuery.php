<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\World\Domain\Entity\Item;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class SearchWorldItemsQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $platform,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /** @return Item[] */
    public function query(QueryParameters $parameters): array
    {
        return $this->denormalizer->denormalize(
            $this->platform->fetch('SELECT id, type, name, short_description as shortDescription FROM world_items'),
            Item::class . '[]',
        );
    }
}
