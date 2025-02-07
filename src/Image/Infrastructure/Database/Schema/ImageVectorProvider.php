<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlDatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class ImageVectorProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
        if ($platform instanceof PgSqlDatabasePlatform) {
            $platform->executeRaw(<<<'SQL'
                 CREATE TABLE images_vectors (
                    image_id uuid PRIMARY KEY,
                    embedding vector(1536) NOT NULL,
                    content text NOT NULL,
                    vector_content_hash text NOT NULL
                 );
            SQL);

            return;
        }

        $platform->executeRaw(<<<'SQL'
            create virtual table images_vectors using vec0(
                image_id text partition key,
                embedding float[1536] distance_metric=cosine,
                +content text,
                +vectorContentHash text
            );
        SQL);
    }
}
