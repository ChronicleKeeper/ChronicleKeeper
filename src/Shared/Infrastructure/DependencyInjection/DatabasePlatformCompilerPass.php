<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\DependencyInjection;

use ChronicleKeeper\Shared\Infrastructure\Database\ConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlConnectionFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlDatabasePlatform;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DatabasePlatformCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasAlias(ConnectionFactory::class)) {
            $container->removeAlias(ConnectionFactory::class);
        }

        $container->setAlias(ConnectionFactory::class, PgSqlConnectionFactory::class);

        if ($container->hasAlias(DatabasePlatform::class)) {
            $container->removeAlias(DatabasePlatform::class);
        }

        $container->setAlias(DatabasePlatform::class, PgSqlDatabasePlatform::class);
    }
}
