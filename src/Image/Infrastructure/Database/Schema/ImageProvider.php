<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class ImageProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
        $platform->query(<<<'SQL'
            CREATE TABLE images (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                mime_type TEXT NOT NULL,
                encoded_image TEXT NOT NULL,
                description TEXT NOT NULL,
                directory TEXT NOT NULL,
                last_updated TEXT NOT NULL,
                FOREIGN KEY(directory) REFERENCES directories(id)
            );
        SQL);

        $platform->query(<<<'SQL'
            CREATE INDEX idx_images_directory
            ON images (directory);
        SQL);
    }
}
