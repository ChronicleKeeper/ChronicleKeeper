<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

use function assert;

final class SearchWorldItemsCountQuery implements Query
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function query(QueryParameters $parameters): int
    {
        assert($parameters instanceof SearchWorldItemsCount);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('COUNT(id) as count')
            ->from('world_items');

        if ($parameters->search !== '') {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->like('name', ':search'),
            )
                ->setParameter('search', '%' . $parameters->search . '%');
        }

        if ($parameters->type !== '') {
            $queryBuilder->andWhere('type = :type')
                ->setParameter('type', $parameters->type);
        }

        if ($parameters->exclude !== []) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->notIn('id', ':excluded_ids'),
            )
                ->setParameter('excluded_ids', $parameters->exclude, ParameterType::STRING);
        }

        $result = $queryBuilder->executeQuery()->fetchOne();

        return (int) $result;
    }
}
