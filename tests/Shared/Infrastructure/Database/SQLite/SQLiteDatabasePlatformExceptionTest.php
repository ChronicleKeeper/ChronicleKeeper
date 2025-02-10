<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite;

use ChronicleKeeper\Shared\Infrastructure\Database\ConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\QueryExecutionFailed;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\StatementPreparationFailed;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\SQLiteDatabasePlatform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SQLite3;
use SQLite3Stmt;

#[CoversClass(SQLiteDatabasePlatform::class)]
#[Group('sqlite')]
#[Small]
final class SQLiteDatabasePlatformExceptionTest extends TestCase
{
    private SQLite3&MockObject $connection;
    private SQLiteDatabasePlatform $platform;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(SQLite3::class);

        $connectionFactory = $this->createMock(ConnectionFactory::class);
        $connectionFactory->method('create')->willReturn($this->connection);

        $this->platform = new SQLiteDatabasePlatform($connectionFactory, new NullLogger());
    }

    #[Test]
    public function fetchThrowsExceptionOnStatementPreparationFailure(): void
    {
        $this->expectException(StatementPreparationFailed::class);

        $this->connection->method('prepare')->willReturn(false);
        $this->platform->fetch('SELECT * FROM test');
    }

    #[Test]
    public function fetchThrowsExceptionOnStatementExecutionFailure(): void
    {
        $this->expectException(QueryExecutionFailed::class);

        $stmt = $this->createMock(SQLite3Stmt::class);
        $stmt->method('execute')->willReturn(false);

        $this->connection->method('prepare')->willReturn($stmt);
        $this->connection->method('lastErrorMsg')->willReturn('Execution failed');

        $this->platform->fetch('SELECT * FROM test');
    }

    #[Test]
    public function theRawExecutionWillFail(): void
    {
        $this->expectException(QueryExecutionFailed::class);

        $this->connection->method('exec')->willReturn(false);

        $this->platform->executeRaw('CREATE TABLE test (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
    }
}
