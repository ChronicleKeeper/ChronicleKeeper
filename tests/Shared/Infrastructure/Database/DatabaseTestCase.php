<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database;

use ChronicleKeeper\Test\WebTestCase;

use function sprintf;

abstract class DatabaseTestCase extends WebTestCase
{
    /** @param non-empty-string $table */
    protected function assertRowsInTable(string $table, int $expectedAmount): void
    {
        $rows = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($table)
            ->executeQuery()
            ->fetchAllAssociative();

        self::assertCount(
            $expectedAmount,
            $rows,
            sprintf('Expected %d rows in the table "%s"', $expectedAmount, $table),
        );
    }

    /** @param non-empty-string $table */
    protected function assertTableIsEmpty(string $table): void
    {
        $this->assertRowsInTable($table, 0);
    }

    /**
     * @param non-empty-string $table
     * @param non-empty-string $column
     *
     * @return array<string, mixed>|null
     */
    protected function getRowFromTable(string $table, string $column, mixed $value): array|null
    {
        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($table)
            ->where(sprintf('%s = :value', $column))
            ->setParameter('value', $value)
            ->executeQuery()
            ->fetchAssociative();

        return $result !== false ? $result : null;
    }
}
