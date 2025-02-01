<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database;

use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\QueryBuilderFactory;

interface DatabasePlatform
{
    public function createQueryBuilder(): QueryBuilderFactory;

    /**
     * @param array<string, mixed> $parameters
     *
     * @return list<mixed>
     */
    public function fetch(string $sql, array $parameters = []): array;

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>|null
     */
    public function fetchSingleRow(string $sql, array $parameters = []): array|null;

    /** @param array<string, mixed> $parameters */
    public function hasRows(string $table, array $parameters = []): bool;

    /** @param array<string, mixed> $parameters */
    public function query(string $sql, array $parameters = []): void;

    /** @param array<string, mixed> $data */
    public function insert(string $table, array $data): void;

    /** @param array<string, mixed> $data */
    public function insertOrUpdate(string $table, array $data): void;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;

    public function truncateTable(string $table): void;
}
