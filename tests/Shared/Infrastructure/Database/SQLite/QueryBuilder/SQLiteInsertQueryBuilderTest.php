<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteInsertQueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SQLiteInsertQueryBuilder::class)]
#[Small]
final class SQLiteInsertQueryBuilderTest extends TestCase
{
    private DatabasePlatform&MockObject $databasePlatform;
    private SQLiteInsertQueryBuilder $builder;

    protected function setUp(): void
    {
        $this->databasePlatform = $this->createMock(DatabasePlatform::class);
        $this->builder          = new SQLiteInsertQueryBuilder($this->databasePlatform);
    }

    protected function tearDown(): void
    {
        unset($this->databasePlatform, $this->builder);
    }

    #[Test]
    public function itBuildsABasicInsertQuery(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('query')
            ->with(
                'INSERT INTO test_table (name) VALUES (:name)',
                ['name' => 'John Doe'],
            );

        $this->builder
            ->insert('test_table')
            ->values(['name' => 'John Doe']);

        $this->builder->execute();
    }

    #[Test]
    public function itBuildsABasicReplaceQuery(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('query')
            ->with(
                'REPLACE INTO test_table (name) VALUES (:name)',
                ['name' => 'John Doe'],
            );

        $this->builder
            ->insert('test_table')
            ->asReplace()
            ->values(['name' => 'John Doe']);

        $this->builder->execute();
    }

    #[Test]
    public function itBuildsAnInsertQueryWithMultipleColumns(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('query')
            ->with(
                'INSERT INTO test_table (name, age, active) VALUES (:name, :age, :active)',
                [
                    'name' => 'John Doe',
                    'age' => 30,
                    'active' => true,
                ],
            );

        $this->builder
            ->insert('test_table')
            ->values([
                'name' => 'John Doe',
                'age' => 30,
                'active' => true,
            ]);

        $this->builder->execute();
    }

    #[Test]
    public function itHandlesNullValues(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('query')
            ->with(
                'INSERT INTO test_table (name, deleted_at) VALUES (:name, :deleted_at)',
                [
                    'name' => 'John Doe',
                    'deleted_at' => null,
                ],
            );

        $this->builder
            ->insert('test_table')
            ->values([
                'name' => 'John Doe',
                'deleted_at' => null,
            ]);

        $this->builder->execute();
    }
}
