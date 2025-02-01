<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteDeleteQueryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SQLiteDeleteQueryBuilder::class)]
#[Small]
class SQLiteDeleteQueryBuilderTest extends TestCase
{
    private DatabasePlatformMock $databasePlatform;
    private SQLiteDeleteQueryBuilder $builder;

    protected function setUp(): void
    {
        $this->databasePlatform = new DatabasePlatformMock();
        $this->builder          = new SQLiteDeleteQueryBuilder($this->databasePlatform);
    }

    #[Test]
    public function itBuildsADeleteQueryWithoutWhere(): void
    {
        $this->builder->from('test_table');

        $this->builder->execute();

        $this->databasePlatform->assertExecutedQuery('DELETE FROM test_table');
    }

    #[Test]
    public function itBuildsABasicDeleteQuery(): void
    {
        $this->builder
            ->from('test_table')
            ->where('id', '=', '123');

        $this->builder->execute();

        // Assert the query was executed with correct SQL and parameters
        $this->databasePlatform->assertExecutedQuery(
            'DELETE FROM test_table WHERE id = :id',
            ['id' => '123'],
        );
    }

    #[Test]
    public function itBuildsADeleteQueryWithMultipleConditions(): void
    {
        $this->builder
            ->from('test_table')
            ->where('id', '=', '123')
            ->where('status', '=', 'active');

        $this->builder->execute();

        // Assert the query was executed with correct SQL and parameters
        $this->databasePlatform->assertExecutedQuery(
            'DELETE FROM test_table WHERE id = :id AND status = :status',
            ['id' => '123', 'status' => 'active'],
        );
    }
}
