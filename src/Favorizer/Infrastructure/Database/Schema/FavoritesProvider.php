<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;
use Doctrine\DBAL\Connection;
use Override;

final class FavoritesProvider extends DefaultSchemaProvider
{
    #[Override]
    public function getPriority(): int
    {
        return 0;
    }

    #[Override]
    public function createSchema(Connection $connection): void
    {
        // Create favorites table
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS favorites (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                type TEXT NOT NULL
            );
        SQL);
    }
}
