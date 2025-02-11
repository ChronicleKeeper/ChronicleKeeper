<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\PgSql;

use ChronicleKeeper\Shared\Infrastructure\Database\Exception\MissingResults;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\UnambiguousResult;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlDatabasePlatform;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PDOException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PgSqlConnectionFactory::class)]
#[CoversClass(PgSqlDatabasePlatform::class)]
#[Group('pgsql')]
final class PgSqlDatabasePlatformTest extends DatabaseTestCase
{
    #[Override]
    protected static function willSetupSchema(): bool
    {
        return false;
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::assertInstanceOf(
            PgSqlDatabasePlatform::class,
            $this->databasePlatform,
            'The test class must be executed in pgsql context.',
        );

        $this->databasePlatform->executeRaw('DROP TABLE IF EXISTS test');
        $this->databasePlatform->executeRaw('CREATE TABLE test (id SERIAL PRIMARY KEY, name TEXT)');

        // Setup test data
        $this->databasePlatform->executeRaw("INSERT INTO test (name) VALUES ('test1')");
        $this->databasePlatform->executeRaw("INSERT INTO test (name) VALUES ('test2')");
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->databasePlatform->executeRaw('DROP TABLE IF EXISTS test');

        parent::tearDown();
    }

    #[Test]
    public function fetchOneReturnsExpectedResult(): void
    {
        $result = $this->databasePlatform->fetchOne(
            'SELECT * FROM test WHERE name = :name',
            ['name' => 'test1'],
        );

        self::assertSame('test1', $result['name']);
    }

    #[Test]
    public function fetchOneThrowsExceptionOnNoResults(): void
    {
        $this->expectException(MissingResults::class);

        $this->databasePlatform->fetchOne(
            'SELECT * FROM test WHERE name = :name',
            ['name' => 'non-existent'],
        );
    }

    #[Test]
    public function fetchOneThrowsExceptionOnMultipleResults(): void
    {
        $this->expectException(UnambiguousResult::class);

        $this->databasePlatform->fetchOne('SELECT * FROM test');
    }

    #[Test]
    public function fetchOneOrNullReturnsExpectedResult(): void
    {
        $result = $this->databasePlatform->fetchOneOrNull(
            'SELECT * FROM test WHERE name = :name',
            ['name' => 'test1'],
        );

        self::assertNotNull($result);
        self::assertSame('test1', $result['name']);
    }

    #[Test]
    public function fetchOneOrNullReturnsNullOnNoResults(): void
    {
        $result = $this->databasePlatform->fetchOneOrNull(
            'SELECT * FROM test WHERE name = :name',
            ['name' => 'non-existent'],
        );

        self::assertNull($result);
    }

    #[Test]
    public function fetchOneOrNullThrowsExceptionOnMultipleResults(): void
    {
        $this->expectException(UnambiguousResult::class);

        $this->databasePlatform->fetchOneOrNull('SELECT * FROM test');
    }

    #[Test]
    public function transactionManagementWorksAsExpected(): void
    {
        $this->databasePlatform->beginTransaction();
        $this->databasePlatform->executeRaw("INSERT INTO test (name) VALUES ('test3')");
        $this->databasePlatform->commit();

        $result = $this->databasePlatform->fetch('SELECT * FROM test WHERE name = :name', ['name' => 'test3']);
        self::assertCount(1, $result);
    }

    #[Test]
    public function transactionRollbackWorksAsExpected(): void
    {
        $this->databasePlatform->beginTransaction();
        $this->databasePlatform->executeRaw("INSERT INTO test (name) VALUES ('test4')");
        $this->databasePlatform->rollback();

        $result = $this->databasePlatform->fetch('SELECT * FROM test WHERE name = :name', ['name' => 'test4']);
        self::assertCount(0, $result);
    }

    #[Test]
    public function invalidSqlThrowsException(): void
    {
        $this->expectException(PDOException::class);

        $this->databasePlatform->executeRaw('INVALID SQL');
    }
}
