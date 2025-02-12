<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\PgSql;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\PgSqlDeleteQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\PgSqlInsertQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\PgSqlSelectQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\PgSqlUpdateQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\DeleteQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\InsertQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\QueryBuilderFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\SelectQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\UpdateQueryBuilder;

final class PgSqlQueryBuilderFactory implements QueryBuilderFactory
{
    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function createSelect(): SelectQueryBuilder
    {
        return new PgSqlSelectQueryBuilder($this->platform);
    }

    public function createInsert(): InsertQueryBuilder
    {
        return new PgSqlInsertQueryBuilder($this->platform);
    }

    public function createUpdate(): UpdateQueryBuilder
    {
        return new PgSqlUpdateQueryBuilder($this->platform);
    }

    public function createDelete(): DeleteQueryBuilder
    {
        return new PgSqlDeleteQueryBuilder($this->platform);
    }
}
