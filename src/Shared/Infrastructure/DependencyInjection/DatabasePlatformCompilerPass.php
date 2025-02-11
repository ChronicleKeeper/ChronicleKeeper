<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\DependencyInjection;

use ChronicleKeeper\Shared\Infrastructure\Database\ConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlDatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\SQLiteConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\SQLiteDatabasePlatform;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function sprintf;

class DatabasePlatformCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $databaseType = $_ENV['DATABASE_TYPE'] ?? 'sqlite';

        $connectionFactory = match ($databaseType) {
            'sqlite' => SQLiteConnectionFactory::class,
            'PgSql' => PgSqlConnectionFactory::class,
            // Add other database types here
            default => throw new RuntimeException(sprintf('Unsupported database type "%s"', $databaseType)),
        };

        if ($container->hasAlias(ConnectionFactory::class)) {
            $container->removeAlias(ConnectionFactory::class);
        }

        $container->setAlias(ConnectionFactory::class, $connectionFactory);

        $platformClass = match ($databaseType) {
            'sqlite' => SQLiteDatabasePlatform::class,
            'PgSql' => PgSqlDatabasePlatform::class,
            // Add other database types here
            default => throw new RuntimeException(sprintf('Unsupported database type "%s"', $databaseType)),
        };

        if ($container->hasAlias(DatabasePlatform::class)) {
            $container->removeAlias(DatabasePlatform::class);
        }

        $container->setAlias(DatabasePlatform::class, $platformClass);
    }
}
