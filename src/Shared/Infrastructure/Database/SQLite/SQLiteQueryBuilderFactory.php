<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\DeleteQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\InsertQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\QueryBuilderFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\SelectQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\UpdateQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteDeleteQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteInsertQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteSelectQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteUpdateQueryBuilder;

final readonly class SQLiteQueryBuilderFactory implements QueryBuilderFactory
{
    public function __construct(private DatabasePlatform $platform)
    {
    }

    public function createSelect(): SelectQueryBuilder
    {
        return new SQLiteSelectQueryBuilder($this->platform);
    }

    public function createInsert(): InsertQueryBuilder
    {
        return new SQLiteInsertQueryBuilder($this->platform);
    }

    public function createUpdate(): UpdateQueryBuilder
    {
        return new SQLiteUpdateQueryBuilder($this->platform);
    }

    public function createDelete(): DeleteQueryBuilder
    {
        return new SQLiteDeleteQueryBuilder($this->platform);
    }
}
