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

    /** @param list<array{0: string, 1: string, 2: string}> $conditions */
    public function orWhere(array $conditions): self;

    /**
     * @param non-empty-string $column
     * @param 'ASC'|'DESC'     $direction
     */
    public function orderBy(string $column, string $direction = 'ASC'): self;

    public function limit(int $limit): self;

    public function offset(int $offset): self;

    /** @return array<int, array<string, mixed>> */
    public function fetchAll(): array;

    /** @return array<string, mixed>|null */
    public function fetchOneOrNull(): array|null;

    /** @return array<string, mixed> */
    public function fetchOne(): array;

    /**
     * @param non-empty-string $embeddingColumn
     * @param list<float>      $vectors
     * @param non-empty-string $distanceColumn
     */
    public function withVectorSearch(
        string $embeddingColumn,
        array $vectors,
        string $distanceColumn,
        float $maxDistance,
    ): self;

    /**
     * @param non-empty-string $embeddingColumn
     * @param non-empty-string $outputAlias
     */
    public function vectorToJson(string $embeddingColumn, string $outputAlias): self;
}
