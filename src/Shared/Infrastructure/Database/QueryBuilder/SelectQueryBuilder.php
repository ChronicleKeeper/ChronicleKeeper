<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder;

interface SelectQueryBuilder extends BaseQueryBuilder
{
    /** @param non-empty-string ...$columns */
    public function select(string ...$columns): self;

    /** @param non-empty-string $table */
    public function from(string $table): self;

    /**
     * @param non-empty-string $column
     * @param non-empty-string $operator
     */
    public function where(string $column, string $operator, mixed $value): self;

    /**
     * @param non-empty-string $column
     * @param 'ASC'|'DESC'     $direction
     */
    public function orderBy(string $column, string $direction = 'ASC'): self;

    /** @param int<1, max> $limit */
    public function limit(int $limit): self;

    /** @return array<int, array<string, mixed>> */
    public function fetchAll(): array;

    /** @return array<string, mixed>|null */
    public function fetchOneOrNull(): array|null;

    /** @return array<string, mixed> */
    public function fetchOne(): array;
}
