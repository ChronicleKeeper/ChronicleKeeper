<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder;

interface BaseQueryBuilder
{
    /**
     * @param non-empty-string $column
     * @param non-empty-string $operator
     */
    public function where(string $column, string $operator, mixed $value): self;

    public function execute(): mixed;
}
