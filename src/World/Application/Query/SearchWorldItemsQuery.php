<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\World\Domain\Entity\Item;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

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
        assert($parameters instanceof SearchWorldItems);

        $queryBuilder = $this->platform->createQueryBuilder()->createSelect()
            ->select('id', 'type', 'name', 'short_description as "shortDescription"')
            ->from('world_items')
            ->orderBy('name');

        if ($parameters->search !== '') {
            $queryBuilder->where('name', 'LIKE', '%' . $parameters->search . '%');
        }

        if ($parameters->type !== '') {
            $queryBuilder->where('type', '=', $parameters->type);
        }

        if ($parameters->exclude !== []) {
            $queryBuilder->where('id', 'NOT IN', $parameters->exclude);
        }

        if ($parameters->limit !== -1) {
            $queryBuilder->limit($parameters->limit);
            $queryBuilder->offset($parameters->offset);
        }

        return $this->denormalizer->denormalize(
            $queryBuilder->fetchAll(),
            Item::class . '[]',
        );
    }
}
