<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteInsertQueryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SQLiteInsertQueryBuilder::class)]
#[Small]
final class SQLiteInsertQueryBuilderTest extends TestCase
{
    private DatabasePlatformMock $databasePlatform;
    private SQLiteInsertQueryBuilder $builder;

    protected function setUp(): void
    {
        $this->databasePlatform = new DatabasePlatformMock();
        $this->builder          = new SQLiteInsertQueryBuilder($this->databasePlatform);
    }

    #[Test]
    public function itBuildsABasicInsertQuery(): void
    {
        $this->builder
            ->insert('test_table')
            ->values(['name' => 'John Doe']);

        $this->builder->execute();

        $this->databasePlatform->assertExecutedQuery(
            'INSERT INTO test_table (name) VALUES (:name)',
            ['name' => 'John Doe'],
        );
    }

    #[Test]
    public function itBuildsABasicReplaceQuery(): void
    {
        $this->builder
            ->insert('test_table')
            ->asReplace()
            ->values(['name' => 'John Doe']);

        $this->builder->execute();

        $this->databasePlatform->assertExecutedQuery(
            'REPLACE INTO test_table (name) VALUES (:name)',
            ['name' => 'John Doe'],
        );
    }

    #[Test]
    public function itBuildsAnInsertQueryWithMultipleColumns(): void
    {
        $this->builder
            ->insert('test_table')
            ->values([
                'name' => 'John Doe',
                'age' => 30,
                'active' => true,
            ]);

        $this->builder->execute();

        $this->databasePlatform->assertExecutedQuery(
            'INSERT INTO test_table (name, age, active) VALUES (:name, :age, :active)',
            [
                'name' => 'John Doe',
                'age' => 30,
                'active' => true,
            ],
        );
    }

    #[Test]
    public function itHandlesNullValues(): void
    {
        $this->builder
            ->insert('test_table')
            ->values([
                'name' => 'John Doe',
                'deleted_at' => null,
            ]);

        $this->builder->execute();

        $this->databasePlatform->assertExecutedQuery(
            'INSERT INTO test_table (name, deleted_at) VALUES (:name, :deleted_at)',
            [
                'name' => 'John Doe',
                'deleted_at' => null,
            ],
        );
    }
}
