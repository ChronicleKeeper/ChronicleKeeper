<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\PgSql\SchemaProvider;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlDatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;
use Override;

final class VectorExtensionProvider extends DefaultSchemaProvider
{
    #[Override]
    public function getPriority(): int
    {
        return -999;
    }

    public function createSchema(DatabasePlatform $platform): void
    {
        if (! $platform instanceof PgSqlDatabasePlatform) {
            return;
        }

        $platform->executeRaw(<<<'SQL'
            CREATE EXTENSION IF NOT EXISTS "vector";
        SQL);
    }
}
