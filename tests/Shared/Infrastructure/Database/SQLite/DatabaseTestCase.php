<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite;

use ChronicleKeeper\Test\WebTestCase;

use function sprintf;

abstract class DatabaseTestCase extends WebTestCase
{
    /** @param non-empty-string $table */
    protected function assertRowsInTable(string $table, int $expectedAmount): void
    {
        $rows = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->from($table)
            ->fetchAll();

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
        return $this->databasePlatform->createQueryBuilder()->createSelect()
            ->from($table)
            ->where($column, '=', $value)
            ->fetchOneOrNull();
    }
}
