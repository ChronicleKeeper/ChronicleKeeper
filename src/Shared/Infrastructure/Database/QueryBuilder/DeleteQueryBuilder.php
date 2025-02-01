<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder;

interface DeleteQueryBuilder extends BaseQueryBuilder
{
    /** @param non-empty-string $table */
    public function from(string $table): self;
}
