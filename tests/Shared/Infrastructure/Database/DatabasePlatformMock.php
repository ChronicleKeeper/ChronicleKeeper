<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use PHPUnit\Framework\Assert;
use Throwable;

use function array_key_exists;
use function count;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class DatabasePlatformMock implements DatabasePlatform
{
    /** @var array<int, array{sql: string, parameters: array<string, mixed>}> */
    private array $executedQueries = [];
    /** @var array<int, array{table: string, data: array<string, mixed>}> */
    private array $executedInserts = [];
    private bool $inTransaction    = false;
    /** @var array<int, array{sql: string, parameters: array<string, mixed>, result: list<mixed>}> */
    private array $fetchExpectations = [];
    /** @var array<int, array{sql: string, parameters: array<string, mixed>}> */
    private array $executedFetches = [];
    /** @var array<string, Throwable> */
    private array $throwExceptionOnInsertToTable = [];

    public function beginTransaction(): void
    {
        Assert::assertFalse($this->inTransaction, 'Transaction already started');
        $this->inTransaction     = true;
        $this->executedQueries[] = ['sql' => 'BEGIN TRANSACTION', 'parameters' => []];
    }

    public function commit(): void
    {
        Assert::assertTrue($this->inTransaction, 'No transaction to commit');
        $this->inTransaction     = false;
        $this->executedQueries[] = ['sql' => 'COMMIT', 'parameters' => []];
    }

    public function rollback(): void
    {
        Assert::assertTrue($this->inTransaction, 'No transaction to rollback');
        $this->inTransaction     = false;
        $this->executedQueries[] = ['sql' => 'ROLLBACK', 'parameters' => []];
    }

    public function throwExceptionOnInsertToTable(string $table, Throwable $exception): void
    {
        $this->throwExceptionOnInsertToTable[$table] = $exception;
    }

    /** @param array<string, mixed> $parameters */
    public function assertExecutedQuery(string $sql, array $parameters = []): void
    {
        Assert::assertContains(['sql' => $sql, 'parameters' => $parameters], $this->executedQueries);
    }

    /** @param array<string, mixed> $data */
    public function assertExecutedInsert(string $table, array $data): void
    {
        Assert::assertContains(['table' => $table, 'data' => $data], $this->executedInserts);
    }

    public function assertExecutedInsertsCount(int $count): void
    {
        Assert::assertCount($count, $this->executedInserts);
    }

    public function assertExecutedQueriesCount(int $count): void
    {
        Assert::assertCount($count, $this->executedQueries);
    }

    /**
     * @param array<string, mixed> $parameters
     * @param list<mixed>          $result
     */
    public function expectFetch(string $sql, array $parameters, array $result): void
    {
        $this->fetchExpectations[] = [
            'sql' => $sql,
            'parameters' => $parameters,
            'result' => $result,
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     * @param list<mixed>          $result
     */
    public function expectAnyFetchWithJustParameters(array $parameters, array $result): void
    {
        $this->fetchExpectations[] = [
            'sql' => '*',
            'parameters' => $parameters,
            'result' => $result,
        ];
    }

    /** @param array<string, mixed> $parameters */
    public function assertFetchExecuted(string $sql, array $parameters = []): void
    {
        Assert::assertContains(
            ['sql' => $sql, 'parameters' => $parameters],
            $this->executedFetches,
            sprintf(
                'Expected fetch "%s" with parameters %s was not executed',
                $sql,
                json_encode($parameters, JSON_THROW_ON_ERROR),
            ),
        );
    }

    public function assertFetchCount(int $count): void
    {
        Assert::assertCount($count, $this->executedFetches);
    }

    /** @inheritDoc */
    public function fetch(string $sql, array $parameters = []): array
    {
        $this->executedFetches[] = ['sql' => $sql, 'parameters' => $parameters];

        // Check for explicit fetch query
        foreach ($this->fetchExpectations as $expectation) {
            if ($expectation['sql'] === $sql && $expectation['parameters'] === $parameters) {
                return $expectation['result'];
            }
        }

        // Check for any query
        foreach ($this->fetchExpectations as $expectation) {
            if ($expectation['sql'] === '*' && $expectation['parameters'] === $parameters) {
                return $expectation['result'];
            }
        }

        Assert::fail(sprintf(
            'No fetch expectation found for query "%s" with parameters %s',
            $sql,
            json_encode($parameters, JSON_THROW_ON_ERROR),
        ));
    }

    /** @inheritDoc */
    public function fetchSingleRow(string $sql, array $parameters = []): array|null
    {
        $result = $this->fetch($sql, $parameters);
        Assert::assertLessThan(2, count($result), 'Expected single row, got ' . count($result));

        return $result[0] ?? null;
    }

    /** @inheritDoc */
    public function hasRows(string $table, array $parameters = []): bool
    {
        $count = $this->fetchSingleRow(sprintf('SELECT COUNT(*) as count FROM %s', $table), $parameters);

        return $count !== null && $count['count'] > 0;
    }

    /** @inheritDoc */
    public function query(string $sql, array $parameters = []): void
    {
        $this->executedQueries[] = ['sql' => $sql, 'parameters' => $parameters];
    }

    /** @inheritDoc */
    public function insert(string $table, array $data): void
    {
        if (array_key_exists($table, $this->throwExceptionOnInsertToTable)) {
            throw $this->throwExceptionOnInsertToTable[$table];
        }

        $this->executedInserts[] = ['table' => $table, 'data' => $data];
    }

    /** @inheritDoc */
    public function insertOrUpdate(string $table, array $data): void
    {
        if (array_key_exists($table, $this->throwExceptionOnInsertToTable)) {
            throw $this->throwExceptionOnInsertToTable[$table];
        }

        $this->executedInserts[] = ['table' => $table, 'data' => $data];
    }

    public function truncateTable(string $table): void
    {
        $this->executedQueries[] = ['sql' => 'DELETE FROM ' . $table, 'parameters' => []];
    }
}
