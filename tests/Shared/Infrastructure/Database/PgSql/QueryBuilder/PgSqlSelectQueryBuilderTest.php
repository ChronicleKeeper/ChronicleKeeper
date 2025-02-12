<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\PgSql\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\PgSqlSelectQueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(PgSqlSelectQueryBuilder::class)]
#[Group('pgsql')]
#[Small]
final class PgSqlSelectQueryBuilderTest extends TestCase
{
    private DatabasePlatform&MockObject $databasePlatform;
    private PgSqlSelectQueryBuilder $builder;

    protected function setUp(): void
    {
        $this->databasePlatform = $this->createMock(DatabasePlatform::class);
        $this->builder          = new PgSqlSelectQueryBuilder($this->databasePlatform);
    }

    protected function tearDown(): void
    {
        unset($this->databasePlatform, $this->builder);
    }

    #[Test]
    public function itBuildsBasicSelectQuery(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetch')
            ->with('SELECT * FROM users');

        $this->builder
            ->select('*')
            ->from('users');

        $this->builder->execute();
    }

    #[Test]
    public function itSelectsSpecificColumns(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetch')
            ->with('SELECT "id", "name" FROM users');

        $this->builder
            ->select('id', 'name')
            ->from('users');

        $this->builder->execute();
    }

    #[Test]
    public function itHandlesWhereConditions(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetch')
            ->with(
                'SELECT * FROM users WHERE "status" = :status_1 AND "age" > :age_2',
                ['status_1' => 'active', 'age_2' => 18],
            );

        $this->builder
            ->select('*')
            ->from('users')
            ->where('status', '=', 'active')
            ->where('age', '>', 18);

        $this->builder->execute();
    }

    #[Test]
    public function itHandlesOrderBy(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetch')
            ->with(
                'SELECT * FROM users ORDER BY name ASC, created_at DESC',
                [],
            );

        $this->builder
            ->select('*')
            ->from('users')
            ->orderBy('name', 'ASC')
            ->orderBy('created_at', 'DESC');

        $this->builder->execute();
    }

    #[Test]
    public function itHandlesLimitAndOffset(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetch')
            ->with(
                'SELECT * FROM users LIMIT 10 OFFSET 20',
                [],
            );

        $this->builder
            ->select('*')
            ->from('users')
            ->limit(10)
            ->offset(20);

        $this->builder->execute();
    }

    #[Test]
    public function itFetchesOneResult(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetchOneOrNull')
            ->with(
                'SELECT * FROM users LIMIT 1',
                [],
            )
        ->willReturn(['id' => 1, 'name' => 'Test']);

        $result = $this->builder
            ->select('*')
            ->from('users')
            ->fetchOneOrNull();

        self::assertSame(['id' => 1, 'name' => 'Test'], $result);
    }

    #[Test]
    public function itReturnsNullWhenNoResultFound(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetchOneOrNull')
            ->with(
                'SELECT * FROM users LIMIT 1',
                [],
            );

        $result = $this->builder
            ->select('*')
            ->from('users')
            ->fetchOneOrNull();

        self::assertNull($result);
    }

    #[Test]
    public function itFetchesAllResults(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetch')
            ->with('SELECT * FROM users')
            ->willReturn($expectedResults = [
                ['id' => 1, 'name' => 'Test 1'],
                ['id' => 2, 'name' => 'Test 2'],
            ]);

        $results = $this->builder
            ->select('*')
            ->from('users')
            ->fetchAll();

        self::assertSame($expectedResults, $results);
    }

    #[Test]
    public function itFetchesAllResultsWithLimit(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetch')
            ->with('SELECT * FROM users LIMIT 10')
            ->willReturn($expectedResults = [
                ['id' => 1, 'name' => 'Test 1'],
                ['id' => 2, 'name' => 'Test 2'],
            ]);

        $results = $this->builder
            ->select('*')
            ->from('users')
            ->limit(10)
            ->fetchAll();

        self::assertSame($expectedResults, $results);
    }

    #[Test]
    public function itFetchesAllResultsWithOffset(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetch')
            ->with('SELECT * FROM users OFFSET 20')
            ->willReturn($expectedResults = [
                ['id' => 1, 'name' => 'Test 1'],
                ['id' => 2, 'name' => 'Test 2'],
            ]);

        $results = $this->builder
            ->select('*')
            ->from('users')
            ->offset(20)
            ->fetchAll();

        self::assertSame($expectedResults, $results);
    }

    #[Test]
    public function itFetchesAllResultsWithLimitAndOffset(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetch')
            ->with('SELECT * FROM users LIMIT 10 OFFSET 20')
            ->willReturn($expectedResults = [
                ['id' => 1, 'name' => 'Test 1'],
                ['id' => 2, 'name' => 'Test 2'],
            ]);

        $results = $this->builder
            ->select('*')
            ->from('users')
            ->limit(10)
            ->offset(20)
            ->fetchAll();

        self::assertSame($expectedResults, $results);
    }

    #[Test]
    public function itHandlesVectorSearchCorrectly(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetch')
            ->with(
                'SELECT *, (embedding <=> \'[1,2]\') AS distance FROM users WHERE (embedding <=> \'[1,2]\') < 0.5',
                [],
            );

        $this->builder
            ->select('*')
            ->from('users')
            ->withVectorSearch('embedding', [1, 2], 'distance', 0.5);

        $this->builder->execute();
    }

    #[Test]
    public function itIsAbleToSpecifyEmbeddingFetchColumn(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('fetch')
            ->with(
                'SELECT *, embedding as my_embedding FROM users',
                [],
            );

        $this->builder
            ->select('*')
            ->from('users')
            ->vectorToJson('embedding', 'my_embedding');

        $this->builder->execute();
    }
}
