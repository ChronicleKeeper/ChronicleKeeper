<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlDatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\Traits\WhereClauseBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\DeleteQueryBuilder;

use function sprintf;

final class PgSqlDeleteQueryBuilder implements DeleteQueryBuilder
{
    use WhereClauseBuilder;

    private string $table = '';

    public function __construct(
        private readonly PgSqlDatabasePlatform $platform,
    ) {
    }

    public function from(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function execute(): null
    {
        $this->platform->query($this->getSQL(), $this->parameters);

        return null;
    }

    private function getSQL(): string
    {
        return sprintf(
            'DELETE FROM %s%s',
            $this->table,
            $this->getWhereClause(),
        );
    }
}
