<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\PgSql;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\QueryBuilderFactory;
use PDO;
use RuntimeException;

final class PgSqlDatabasePlatform implements DatabasePlatform
{
    private PDO|null $connection = null;

    public function __construct(
        private readonly PgSqlConnectionFactory $connectionFactory,
    ) {
    }

    public function getConnection(): PDO
    {
        if (! $this->connection instanceof PDO) {
            $this->connection = $this->connectionFactory->create();
        }

        return $this->connection;
    }

    public function createQueryBuilder(): QueryBuilderFactory
    {
        return new PgSqlQueryBuilderFactory($this);
    }

    /** @inheritDoc */
    public function fetch(string $sql, array $parameters = []): array
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($parameters);

        return $stmt->fetchAll();
    }

    /** @inheritDoc */
    public function fetchOne(string $sql, array $parameters = []): array
    {
        $result = $this->fetchOneOrNull($sql, $parameters);
        if ($result === null) {
            throw new RuntimeException('No result found');
        }

        return $result;
    }

    /** @inheritDoc */
    public function fetchOneOrNull(string $sql, array $parameters = []): array|null
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($parameters);
        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    /** @inheritDoc */
    public function query(string $sql, array $parameters = []): mixed
    {
        return $this->getConnection()->prepare($sql)->execute($parameters);
    }

    public function executeRaw(string $sql): void
    {
        $this->getConnection()->exec($sql);
    }

    public function beginTransaction(): void
    {
        $this->getConnection()->beginTransaction();
    }

    public function commit(): void
    {
        $this->getConnection()->commit();
    }

    public function rollback(): void
    {
        $this->getConnection()->rollBack();
    }
}
