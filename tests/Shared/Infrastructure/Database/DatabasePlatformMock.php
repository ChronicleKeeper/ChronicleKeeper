<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use PHPUnit\Framework\Assert;

use function json_encode;
use function sprintf;

final class DatabasePlatformMock implements DatabasePlatform
{
    /** @var array<int, array{sql: string, parameters: array<string, mixed>}> */
    private array $executedQueries = [];
    /** @var array<int, array{table: string, data: array<string, mixed>}> */
    private array $executedInserts = [];
    private bool $inTransaction    = false;
    /** @var array<int, array{sql: string, parameters: array<string, mixed>, result: array<int, mixed>}> */
    private array $fetchExpectations = [];
    /** @var array<int, array{sql: string, parameters: array<string, mixed>}> */
    private array $executedFetches = [];

    public function beginTransaction(): void
    {
        Assert::assertFalse($this->inTransaction, 'Transaction already started');
        $this->inTransaction = true;
    }

    public function commit(): void
    {
        Assert::assertTrue($this->inTransaction, 'No transaction to commit');
        $this->inTransaction = false;
    }

    public function rollback(): void
    {
        Assert::assertTrue($this->inTransaction, 'No transaction to rollback');
        $this->inTransaction = false;
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
     * @param array<mixed>         $result
     */
    public function expectFetch(string $sql, array $parameters, array $result): void
    {
        $this->fetchExpectations[] = [
            'sql' => $sql,
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
            sprintf('Expected fetch "%s" with parameters %s was not executed', $sql, json_encode($parameters)),
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

        foreach ($this->fetchExpectations as $expectation) {
            if ($expectation['sql'] === $sql && $expectation['parameters'] === $parameters) {
                return $expectation['result'];
            }
        }

        Assert::fail(sprintf(
            'No fetch expectation found for query "%s" with parameters %s',
            $sql,
            json_encode($parameters),
        ));
    }

    /** @inheritDoc */
    public function query(string $sql, array $parameters = []): void
    {
        $this->executedQueries[] = ['sql' => $sql, 'parameters' => $parameters];
    }

    /** @inheritDoc */
    public function insert(string $table, array $data): void
    {
        $this->executedInserts[] = ['table' => $table, 'data' => $data];
    }

    /** @inheritDoc */
    public function insertOrUpdate(string $table, array $data): void
    {
        $this->executedInserts[] = ['table' => $table, 'data' => $data];
    }
}
