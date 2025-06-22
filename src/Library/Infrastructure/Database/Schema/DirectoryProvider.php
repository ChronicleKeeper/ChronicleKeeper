<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;
use Doctrine\DBAL\Connection;
use Override;

final class DirectoryProvider extends DefaultSchemaProvider
{
    #[Override]
    public function getPriority(): int
    {
        return 0;
    }

    #[Override]
    public function createSchema(Connection $connection): void
    {
        // Create directories table
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS directories (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                parent TEXT NOT NULL
            );
        SQL);

        // Create index on parent for faster lookups
        $connection->executeStatement(<<<'SQL'
            CREATE INDEX IF NOT EXISTS idx_directories_parent
            ON directories (parent);
        SQL);
    }
}
