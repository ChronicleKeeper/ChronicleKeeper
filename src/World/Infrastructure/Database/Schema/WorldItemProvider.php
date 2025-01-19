<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class WorldItemProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
        $platform->query(<<<'SQL'
        CREATE TABLE world_items (
            id TEXT PRIMARY KEY,
            type TEXT NOT NULL CHECK(type IN (
                'country', 'region', 'location',
                'organization', 'person', 'race',
                'event', 'quest', 'campaign',
                'object', 'other'
            )),
            name TEXT NOT NULL,
            short_description TEXT NOT NULL
        );
    SQL);
    }
}
