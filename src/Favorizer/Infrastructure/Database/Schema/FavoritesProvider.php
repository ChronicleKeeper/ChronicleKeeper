<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;
use Override;

final class FavoritesProvider extends DefaultSchemaProvider
{
    #[Override]
    public function getPriority(): int
    {
        return 0;
    }

    public function createSchema(DatabasePlatform $platform): void
    {
        // Create table for targets
        $platform->executeRaw(
            <<<'SQL'
            CREATE TABLE favorites (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                type TEXT NOT NULL
            );
            SQL,
        );
    }
}
