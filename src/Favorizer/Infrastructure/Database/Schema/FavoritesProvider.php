<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class FavoritesProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
        // Create table for targets
        $platform->query(
            <<<'SQL'
            CREATE TABLE favorites (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                type TEXT NOT NULL
                CHECK(type IN ('ChatConversationTarget', 'LibraryDocumentTarget', 'LibraryImageTarget', 'WorldItemTarget'))
            );
            SQL,
        );
    }
}
