<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteSelectQueryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SQLiteSelectQueryBuilder::class)]
#[Small]
final class SQLiteSelectQueryBuilderTest extends TestCase
{
    private DatabasePlatformMock $databasePlatform;
    private SQLiteSelectQueryBuilder $builder;

    protected function setUp(): void
    {
        $this->databasePlatform = new DatabasePlatformMock();
        $this->builder          = new SQLiteSelectQueryBuilder($this->databasePlatform);
    }

    #[Test]
    public function itBuildsBasicSelectQuery(): void
    {
        $this->databasePlatform->setMockResult([]);

        $this->builder
            ->select('*')
            ->from('users');

        $this->builder->execute();

        $this->databasePlatform->assertFetchExecuted('SELECT * FROM users');
    }

    #[Test]
    public function itSelectsSpecificColumns(): void
    {
        $this->databasePlatform->setMockResult([]);

        $this->builder
            ->select('id', 'name')
            ->from('users');

        $this->builder->execute();

        $this->databasePlatform->assertFetchExecuted(
            'SELECT id, name FROM users',
            [],
        );
    }

    #[Test]
    public function itHandlesWhereConditions(): void
    {
        $this->databasePlatform->setMockResult([]);

        $this->builder
            ->select('*')
            ->from('users')
            ->where('status', '=', 'active')
            ->where('age', '>', 18);

        $this->builder->execute();

        $this->databasePlatform->assertFetchExecuted(
            'SELECT * FROM users WHERE status = :status_1 AND age > :age_2',
            ['status_1' => 'active', 'age_2' => 18],
        );
    }

    #[Test]
    public function itHandlesOrderBy(): void
    {
        $this->databasePlatform->setMockResult([]);

        $this->builder
            ->select('*')
            ->from('users')
            ->orderBy('name', 'ASC')
            ->orderBy('created_at', 'DESC');

        $this->builder->execute();

        $this->databasePlatform->assertFetchExecuted(
            'SELECT * FROM users ORDER BY name ASC, created_at DESC',
            [],
        );
    }

    #[Test]
    public function itHandlesLimitAndOffset(): void
    {
        $this->databasePlatform->setMockResult([]);

        $this->builder
            ->select('*')
            ->from('users')
            ->limit(10)
            ->offset(20);

        $this->builder->execute();

        $this->databasePlatform->assertFetchExecuted(
            'SELECT * FROM users LIMIT 10 OFFSET 20',
            [],
        );
    }

    #[Test]
    public function itFetchesOneResult(): void
    {
        $this->databasePlatform->setMockResult([
            ['id' => 1, 'name' => 'Test'],
        ]);

        $result = $this->builder
            ->select('*')
            ->from('users')
            ->fetchOne();

        $this->databasePlatform->assertFetchExecuted(
            'SELECT * FROM users LIMIT 1',
            [],
        );

        self::assertSame(['id' => 1, 'name' => 'Test'], $result);
    }

    #[Test]
    public function itReturnsNullWhenNoResultFound(): void
    {
        $this->databasePlatform->setMockResult([]);

        $result = $this->builder
            ->select('*')
            ->from('users')
            ->fetchOne();

        self::assertNull($result);
    }

    #[Test]
    public function itFetchesAllResults(): void
    {
        $expectedResults = [
            ['id' => 1, 'name' => 'Test 1'],
            ['id' => 2, 'name' => 'Test 2'],
        ];

        $this->databasePlatform->setMockResult($expectedResults);

        $results = $this->builder
            ->select('*')
            ->from('users')
            ->fetchAll();

        self::assertSame($expectedResults, $results);
    }
}
