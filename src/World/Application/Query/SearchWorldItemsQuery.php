<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\World\Domain\Entity\Item;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;
use function implode;

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

        $query           = 'SELECT id, type, name, short_description as shortDescription FROM world_items';
        $queryParameters = [];
        $addWhere        = [];

        if ($parameters->search !== '') {
            $addWhere[]                = 'name LIKE :search';
            $queryParameters['search'] = '%' . $parameters->search . '%';
        }

        if ($parameters->type !== '') {
            $addWhere[]              = 'type = :type';
            $queryParameters['type'] = $parameters->type;
        }

        if ($parameters->exclude !== []) {
            $addWhere[]                 = 'id NOT IN (:exclude)';
            $queryParameters['exclude'] = implode(',', $parameters->exclude);
        }

        if ($addWhere !== []) {
            $query .= ' WHERE ' . implode(' AND ', $addWhere);
        }

        $query .= ' ORDER BY name ASC';

        return $this->denormalizer->denormalize(
            $this->platform->fetch($query, $queryParameters),
            Item::class . '[]',
        );
    }
}
