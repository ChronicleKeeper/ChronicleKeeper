<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder;

interface DeleteQueryBuilder extends BaseQueryBuilder
{
    /** @param non-empty-string $table */
    public function from(string $table): self;

    /**
     * @param non-empty-string $column
     * @param non-empty-string $operator
     */
    public function where(string $column, string $operator, mixed $value): self;

    /** @param list<array{0: string, 1: string, 2: string}> $conditions */
    public function orWhere(array $conditions): self;
}
