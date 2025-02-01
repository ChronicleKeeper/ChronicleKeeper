<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder;

interface UpdateQueryBuilder extends BaseQueryBuilder
{
    /** @param non-empty-string $table */
    public function update(string $table): self;

    /** @param array<string, mixed> $data */
    public function set(array $data): self;
}
