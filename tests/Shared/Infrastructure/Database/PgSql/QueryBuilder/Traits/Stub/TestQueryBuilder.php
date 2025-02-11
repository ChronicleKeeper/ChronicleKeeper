<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\PgSql\QueryBuilder\Traits\Stub;

use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\Traits\WhereClauseBuilder;

class TestQueryBuilder
{
    use WhereClauseBuilder;

    /** @return array<string, mixed> */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getRenderedWhereClause(): string
    {
        return $this->getWhereClause();
    }
}
