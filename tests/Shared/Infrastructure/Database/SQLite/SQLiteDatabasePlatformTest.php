<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite;

use ChronicleKeeper\Shared\Infrastructure\Database\ConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\MissingResults;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\QueryExecutionFailed;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\UnambiguousResult;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\SQLiteDatabasePlatform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SQLite3;

#[CoversClass(SQLiteDatabasePlatform::class)]
#[CoversClass(MissingResults::class)]
#[CoversClass(UnambiguousResult::class)]
#[Group('sqlite')]
final class SQLiteDatabasePlatformTest extends TestCase
{
    private SQLiteDatabasePlatform $platform;

    protected function setUp(): void
    {
        $connectionFactory = new class implements ConnectionFactory {
            public function create(): SQLite3
            {
                return new SQLite3(':memory:');
            }
        };

        $this->platform = new SQLiteDatabasePlatform($connectionFactory, new NullLogger());

        $this->platform->executeRaw('CREATE TABLE test (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
        $this->platform->executeRaw("INSERT INTO test (name) VALUES ('test1')");
        $this->platform->executeRaw("INSERT INTO test (name) VALUES ('test2')");
    }

    #[Test]
    public function fetchOneReturnsExpectedResult(): void
    {
        $result = $this->platform->fetchOne(
            'SELECT * FROM test WHERE name = :name',
            ['name' => 'test1'],
        );

        self::assertSame('test1', $result['name']);
    }

    #[Test]
    public function fetchOneThrowsExceptionOnNoResults(): void
    {
        $this->expectException(MissingResults::class);

        $this->platform->fetchOne(
            'SELECT * FROM test WHERE name = :name',
            ['name' => 'non-existent'],
        );
    }

    #[Test]
    public function fetchOneThrowsExceptionOnMultipleResults(): void
    {
        $this->expectException(UnambiguousResult::class);

        $this->platform->fetchOne('SELECT * FROM test');
    }

    #[Test]
    public function fetchOneOrNullReturnsExpectedResult(): void
    {
        $result = $this->platform->fetchOneOrNull(
            'SELECT * FROM test WHERE name = :name',
            ['name' => 'test1'],
        );

        self::assertNotNull($result);
        self::assertSame('test1', $result['name']);
    }

    #[Test]
    public function fetchOneOrNullReturnsNullOnNoResults(): void
    {
        $result = $this->platform->fetchOneOrNull(
            'SELECT * FROM test WHERE name = :name',
            ['name' => 'non-existent'],
        );

        self::assertNull($result);
    }

    #[Test]
    public function fetchOneOrNullThrowsExceptionOnMultipleResults(): void
    {
        $this->expectException(UnambiguousResult::class);

        $this->platform->fetchOneOrNull('SELECT * FROM test');
    }

    #[Test]
    public function transactionManagementWorksAsExpected(): void
    {
        $this->platform->beginTransaction();
        $this->platform->executeRaw("INSERT INTO test (name) VALUES ('test3')");
        $this->platform->commit();

        $result = $this->platform->fetch('SELECT * FROM test WHERE name = :name', ['name' => 'test3']);
        self::assertCount(1, $result);
    }

    #[Test]
    public function transactionRollbackWorksAsExpected(): void
    {
        $this->platform->beginTransaction();
        $this->platform->executeRaw("INSERT INTO test (name) VALUES ('test4')");
        $this->platform->rollback();

        $result = $this->platform->fetch('SELECT * FROM test WHERE name = :name', ['name' => 'test4']);
        self::assertEmpty($result);
    }

    #[Test]
    public function invalidSqlThrowsQueryExecutionFailed(): void
    {
        $this->expectException(QueryExecutionFailed::class);

        $this->platform->executeRaw('INVALID SQL');
    }
}
