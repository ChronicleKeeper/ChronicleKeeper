<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\PgSql;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\MissingResults;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\UnambiguousResult;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\QueryBuilderFactory;
use PDO;

use function count;

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
            throw MissingResults::forQuery($sql);
        }

        return $result;
    }

    /** @inheritDoc */
    public function fetchOneOrNull(string $sql, array $parameters = []): array|null
    {
        $result      = $this->fetch($sql, $parameters);
        $resultCount = count($result);

        if ($resultCount === 0) {
            return null;
        }

        if ($resultCount > 1) {
            throw UnambiguousResult::forQuery($sql);
        }

        return $result[0];
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
