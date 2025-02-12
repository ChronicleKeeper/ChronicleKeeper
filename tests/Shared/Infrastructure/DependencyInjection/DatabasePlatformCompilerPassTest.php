<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\DependencyInjection;

use ChronicleKeeper\Shared\Infrastructure\Database\ConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlDatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\SQLiteConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\SQLiteDatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\DependencyInjection\DatabasePlatformCompilerPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(DatabasePlatformCompilerPass::class)]
#[Small]
final class DatabasePlatformCompilerPassTest extends TestCase
{
    private string $environmentVariableState = '';

    public function setUp(): void
    {
        $this->environmentVariableState = $_ENV['DATABASE_TYPE'];
    }

    public function tearDown(): void
    {
        $_ENV['DATABASE_TYPE'] = $this->environmentVariableState;
    }

    #[Test]
    public function itIsUtilizingTheSqliteDatabaseInDefault(): void
    {
        $container = new ContainerBuilder();
        $container->setAlias(ConnectionFactory::class, PgSqlConnectionFactory::class);
        $container->setAlias(DatabasePlatform::class, PgSqlDatabasePlatform::class);

        unset($_ENV['DATABASE_TYPE']); // Unset the environment variable to use the default database type

        $compilerPass = new DatabasePlatformCompilerPass();
        $compilerPass->process($container);

        self::assertSame(SQLiteConnectionFactory::class, (string) $container->getAlias(ConnectionFactory::class));
        self::assertSame(SQLiteDatabasePlatform::class, (string) $container->getAlias(DatabasePlatform::class));
    }

    #[Test]
    public function itIsUtilizingTheSqliteDatabaseOnEnvironmentVariable(): void
    {
        $container = new ContainerBuilder();
        $container->setAlias(ConnectionFactory::class, PgSqlConnectionFactory::class);
        $container->setAlias(DatabasePlatform::class, PgSqlDatabasePlatform::class);

        $_ENV['DATABASE_TYPE'] = 'sqlite';

        $compilerPass = new DatabasePlatformCompilerPass();
        $compilerPass->process($container);

        self::assertSame(SQLiteConnectionFactory::class, (string) $container->getAlias(ConnectionFactory::class));
        self::assertSame(SQLiteDatabasePlatform::class, (string) $container->getAlias(DatabasePlatform::class));
    }

    #[Test]
    public function itIsUtilizingThePgSqlConnectionFactoryOnEnvironmentVariable(): void
    {
        $container = new ContainerBuilder();
        $container->setAlias(ConnectionFactory::class, SQLiteConnectionFactory::class);
        $container->setAlias(DatabasePlatform::class, SQLiteDatabasePlatform::class);

        $_ENV['DATABASE_TYPE'] = 'PgSql';

        $compilerPass = new DatabasePlatformCompilerPass();
        $compilerPass->process($container);

        self::assertSame(PgSqlConnectionFactory::class, (string) $container->getAlias(ConnectionFactory::class));
        self::assertSame(PgSqlDatabasePlatform::class, (string) $container->getAlias(DatabasePlatform::class));
    }

    #[Test]
    public function itThrowsAnExceptionOnUnsupportedDatabaseType(): void
    {
        $this->expectExceptionMessage('Unsupported database type "unsupported"');

        $container = new ContainerBuilder();
        $container->setAlias(ConnectionFactory::class, SQLiteConnectionFactory::class);
        $container->setAlias(DatabasePlatform::class, SQLiteDatabasePlatform::class);

        $_ENV['DATABASE_TYPE'] = 'unsupported';

        $compilerPass = new DatabasePlatformCompilerPass();
        $compilerPass->process($container);
    }
}
