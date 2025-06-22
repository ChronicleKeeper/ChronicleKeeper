<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Schema;

use Doctrine\DBAL\Connection;
use Override;

final class VectorExtensionProvider extends DefaultSchemaProvider
{
    #[Override]
    public function getPriority(): int
    {
        return -999;
    }

    #[Override]
    public function createSchema(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
            CREATE EXTENSION IF NOT EXISTS "vector";
        SQL);
    }
}
