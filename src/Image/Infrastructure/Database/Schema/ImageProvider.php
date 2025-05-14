<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;
use Doctrine\DBAL\Connection;
use Override;

final class ImageProvider extends DefaultSchemaProvider
{
    #[Override]
    public function getPriority(): int
    {
        return 10;
    }

    #[Override]
    public function createSchema(Connection $connection): void
    {
        // Create images table
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS images (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                mime_type TEXT NOT NULL,
                encoded_image TEXT NOT NULL,
                description TEXT NOT NULL,
                directory TEXT NOT NULL,
                last_updated TEXT NOT NULL
            );
        SQL);

        // Create index on directory for faster lookups
        $connection->executeStatement(<<<'SQL'
            CREATE INDEX IF NOT EXISTS idx_images_directory
            ON images (directory);
        SQL);
    }
}
