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
     * @return array<int, array<string, mixed>>
     */
    public function fetch(string $sql, array $parameters = []): array;

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    public function fetchOne(string $sql, array $parameters = []): array;

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>|null
     */
    public function fetchOneOrNull(string $sql, array $parameters = []): array|null;

    /** @param array<string, mixed> $parameters */
    public function query(string $sql, array $parameters = []): mixed;

    public function executeRaw(string $sql): void;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
