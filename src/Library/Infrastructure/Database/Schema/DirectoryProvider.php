<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class DirectoryProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
        $platform->query(<<<'SQL'
            CREATE TABLE directories (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                parent TEXT NOT NULL,
                FOREIGN KEY(parent) REFERENCES directories(id)
            );
        SQL);

        $platform->query(<<<'SQL'
            CREATE INDEX idx_directories_parent
            ON directories (parent);
        SQL);
    }
}
