<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class ImageVectorProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
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
