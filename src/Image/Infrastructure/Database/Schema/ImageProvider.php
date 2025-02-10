<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class ImageProvider extends DefaultSchemaProvider
{
    public function getPriority(): int
    {
        return 10;
    }

    public function createSchema(DatabasePlatform $platform): void
    {
        $platform->executeRaw(<<<'SQL'
            CREATE TABLE images (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                mime_type TEXT NOT NULL,
                encoded_image TEXT NOT NULL,
                description TEXT NOT NULL,
                directory TEXT NOT NULL,
                last_updated TEXT NOT NULL
            );
        SQL);

        $platform->executeRaw(<<<'SQL'
            CREATE INDEX idx_images_directory
            ON images (directory);
        SQL);
    }
}
