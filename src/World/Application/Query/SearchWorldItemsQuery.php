<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\World\Domain\Entity\Item;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

final readonly class SearchWorldItemsQuery implements Query
{
    public function __construct(
        private Connection $connection,
        private DenormalizerInterface $denormalizer,
    ) {
    }

    /** @return Item[] */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof SearchWorldItems);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('id', 'type', 'name', 'short_description as "shortDescription"')
            ->from('world_items')
            ->orderBy('name');

        if ($parameters->search !== '') {
            $queryBuilder->andWhere('name LIKE :search')
                ->setParameter('search', '%' . $parameters->search . '%');
        }

        if ($parameters->type !== '') {
            $queryBuilder->andWhere('type = :type')
                ->setParameter('type', $parameters->type);
        }

        if ($parameters->exclude !== []) {
            $queryBuilder->andWhere('id NOT IN (:exclude)')
                ->setParameter('exclude', $parameters->exclude);
        }

        if ($parameters->limit !== -1) {
            $queryBuilder->setMaxResults($parameters->limit)
                ->setFirstResult($parameters->offset);
        }

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();

        return $this->denormalizer->denormalize(
            $results,
            Item::class . '[]',
        );
    }
}
