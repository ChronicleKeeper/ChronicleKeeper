<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;
use Doctrine\DBAL\Connection;
use Override;

final class DocumentProvider extends DefaultSchemaProvider
{
    #[Override]
    public function getPriority(): int
    {
        return 10;
    }

    /**
     * Creates database schema for documents
     */
    #[Override]
    public function createSchema(Connection $connection): void
    {
        // Create documents table
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS documents (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                content TEXT NOT NULL,
                directory TEXT NOT NULL,
                last_updated TEXT NOT NULL
            );
        SQL);

        // Create index on directory for faster lookups
        $connection->executeStatement(<<<'SQL'
            CREATE INDEX IF NOT EXISTS idx_documents_directory
            ON documents (directory);
        SQL);

        // Create index on last_updated for time-based queries
        $connection->executeStatement(<<<'SQL'
            CREATE INDEX IF NOT EXISTS idx_documents_last_updated
            ON documents (last_updated);
        SQL);
    }
}
