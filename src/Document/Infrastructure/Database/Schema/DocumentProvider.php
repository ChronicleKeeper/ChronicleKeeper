<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class DocumentProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
        $platform->query(<<<'SQL'
            CREATE TABLE documents (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                content TEXT NOT NULL,
                directory TEXT NOT NULL,
                last_updated TEXT NOT NULL,
                FOREIGN KEY(directory) REFERENCES directories(id)
            );
        SQL);

        $platform->query(<<<'SQL'
            CREATE INDEX idx_documents_directory
            ON documents (directory);
        SQL);

        $platform->query(<<<'SQL'
            CREATE INDEX idx_documents_last_updated
            ON documents (last_updated);
        SQL);
    }
}
