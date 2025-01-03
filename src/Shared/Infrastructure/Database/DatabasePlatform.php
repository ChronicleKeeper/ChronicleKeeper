<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database;

interface DatabasePlatform
{
    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<int, mixed>
     */
    public function fetch(string $sql, array $parameters = []): array;

    /** @param array<string, mixed> $parameters */
    public function query(string $sql, array $parameters = []): void;

    /** @param array<string, mixed> $data */
    public function insert(string $table, array $data): void;

    /** @param array<string, mixed> $data */
    public function insertOrUpdate(string $table, array $data): void;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
