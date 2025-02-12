<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder;

interface UpdateQueryBuilder extends BaseQueryBuilder
{
    /** @param non-empty-string $table */
    public function update(string $table): self;

    /**
     * @param non-empty-string $column
     * @param non-empty-string $operator
     */
    public function where(string $column, string $operator, mixed $value): self;

    /** @param array<string, mixed> $data */
    public function set(array $data): self;
}
