<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder;

interface BaseQueryBuilder
{
    public function execute(): mixed;
}
