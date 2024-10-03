<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Exception\QueryNotExists;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class QueryService
{
    /** @var array<class-string, Query> */
    private array $queries;

    /** @param iterable<Query> $queries */
    public function __construct(
        #[AutowireIterator('app.shared.application.query')]
        iterable $queries,
    ) {
        foreach ($queries as $query) {
            $this->queries[$query::class] = $query;
        }
    }

    public function query(QueryParameters $parameters): mixed
    {
        $queryClass = $parameters->getQueryClass();

        return ($this->queries[$queryClass] ?? throw new QueryNotExists($queryClass))->query($parameters);
    }
}
