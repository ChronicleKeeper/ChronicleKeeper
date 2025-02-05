<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteDeleteQueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SQLiteDeleteQueryBuilder::class)]
#[Small]
class SQLiteDeleteQueryBuilderTest extends TestCase
{
    private DatabasePlatform&MockObject $databasePlatform;
    private SQLiteDeleteQueryBuilder $builder;

    protected function setUp(): void
    {
        $this->databasePlatform = $this->createMock(DatabasePlatform::class);
        $this->builder          = new SQLiteDeleteQueryBuilder($this->databasePlatform);
    }

    protected function tearDown(): void
    {
        unset($this->databasePlatform, $this->builder);
    }

    #[Test]
    public function itBuildsADeleteQueryWithoutWhere(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('query')
            ->with('DELETE FROM test_table');

        $this->builder->from('test_table');
        $this->builder->execute();
    }

    #[Test]
    public function itBuildsABasicDeleteQuery(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('query')
            ->with('DELETE FROM test_table WHERE id = :id_1', ['id_1' => '123']);

        $this->builder
            ->from('test_table')
            ->where('id', '=', '123');

        $this->builder->execute();
    }

    #[Test]
    public function itBuildsADeleteQueryWithMultipleConditions(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('query')
            ->with(
                'DELETE FROM test_table WHERE id = :id_1 AND status = :status_2',
                ['id_1' => '123', 'status_2' => 'active'],
            );

        $this->builder
            ->from('test_table')
            ->where('id', '=', '123')
            ->where('status', '=', 'active');

        $this->builder->execute();
    }
}
