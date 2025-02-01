<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder;

interface InsertQueryBuilder extends BaseQueryBuilder
{
    /**
     * Marks the insert query to replace a dataset instead of just inserting it.
     * So when the dataset already exist it should be replaced or updated by the new one.
     */
    public function asReplace(): self;

    /** @param non-empty-string $table */
    public function insert(string $table): self;

    /** @param array<string, mixed> $data */
    public function values(array $data): self;
}
