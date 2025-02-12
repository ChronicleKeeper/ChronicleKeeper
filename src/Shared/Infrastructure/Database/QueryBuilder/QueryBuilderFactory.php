<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder;

interface QueryBuilderFactory
{
    public function createSelect(): SelectQueryBuilder;

    public function createInsert(): InsertQueryBuilder;

    public function createUpdate(): UpdateQueryBuilder;

    public function createDelete(): DeleteQueryBuilder;
}
