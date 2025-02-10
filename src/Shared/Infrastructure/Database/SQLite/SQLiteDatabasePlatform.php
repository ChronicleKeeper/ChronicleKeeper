<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite;

use ChronicleKeeper\Shared\Infrastructure\Database\ConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\MissingResults;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\QueryExecutionFailed;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\StatementPreparationFailed;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\UnambiguousResult;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\QueryBuilderFactory;
use Psr\Log\LoggerInterface;
use SQLite3;
use SQLite3Result;
use Throwable;

use function assert;
use function count;
use function sprintf;

use const SQLITE3_ASSOC;

class SQLiteDatabasePlatform implements DatabasePlatform
{
    private SQLite3|null $connection                      = null;
    private QueryBuilderFactory|null $queryBuilderFactory = null;

    public function __construct(
        private readonly ConnectionFactory $connectionFactory,
        private readonly LoggerInterface $databaseLogger,
    ) {
    }

    public function createQueryBuilder(): QueryBuilderFactory
    {
        if (! $this->queryBuilderFactory instanceof QueryBuilderFactory) {
            $this->queryBuilderFactory = new SQLiteQueryBuilderFactory($this);
        }

        return $this->queryBuilderFactory;
    }

    private function getConnection(): SQLite3
    {
        if (! $this->connection instanceof SQLite3) {
            $this->databaseLogger->debug('Establish SQLite Connection');
            $this->connection = $this->connectionFactory->create();
            assert($this->connection instanceof SQLite3);
        }

        return $this->connection;
    }

    /** @inheritDoc */
    public function fetch(string $sql, array $parameters = []): array
    {
        $statementResult = $this->query($sql, $parameters);

        $result = [];
        while ($row = $statementResult->fetchArray(SQLITE3_ASSOC)) {
            $result[] = $row;
        }

        return $result;
    }

    /** @inheritDoc */
    public function fetchOne(string $sql, array $parameters = []): array
    {
        $result = $this->fetchOneOrNull($sql, $parameters);
        if ($result === null) {
            $this->databaseLogger->warning(
                'Expected a single result, but got none',
                ['sql' => $sql, 'parameters' => $parameters],
            );

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
            $this->databaseLogger->warning(
                sprintf('Expected a single result, but got %d', $resultCount),
                ['sql' => $sql, 'parameters' => $parameters],
            );

            throw UnambiguousResult::forQuery($sql);
        }

        return $result[0];
    }

    /** @param array<string, mixed> $parameters */
    public function query(string $sql, array $parameters = []): SQLite3Result
    {
        $stmt = $this->getConnection()->prepare($sql);
        if ($stmt === false) {
            $this->databaseLogger->error('Failed to prepare statement', ['sql' => $sql, 'parameters' => $parameters]);

            throw StatementPreparationFailed::forQuery($sql);
        }

        foreach ($parameters as $item => $value) {
            $stmt->bindValue(':' . $item, $value);
        }

        $statementResult = $stmt->execute();

        if ($statementResult === false) {
            $this->databaseLogger->debug('Failed to execute statement', ['sql' => $sql, 'parameters' => $parameters]);

            throw QueryExecutionFailed::forStatement($sql, $this->getConnection()->lastErrorMsg());
        }

        return $statementResult;
    }

    public function executeRaw(string $sql): void
    {
        try {
            if (! $this->getConnection()->exec($sql)) {
                $this->databaseLogger->error('Failed to execute statement', ['sql' => $sql]);

                throw QueryExecutionFailed::forQuery($sql);
            }
        } catch (Throwable $e) {
            throw QueryExecutionFailed::forQuery($sql, $e);
        }
    }

    public function beginTransaction(): void
    {
        $this->databaseLogger->debug('SQL: BEGIN TRANSACTION');
        $this->getConnection()->exec('BEGIN TRANSACTION');
    }

    public function commit(): void
    {
        $this->databaseLogger->debug('SQL: COMMIT');
        $this->getConnection()->exec('COMMIT');
    }

    public function rollback(): void
    {
        $this->databaseLogger->debug('SQL: ROLLBACK');
        $this->getConnection()->exec('ROLLBACK');
    }
}
