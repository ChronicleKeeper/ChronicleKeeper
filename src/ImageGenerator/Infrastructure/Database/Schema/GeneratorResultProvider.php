<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class GeneratorResultProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
        $platform->executeRaw(<<<'SQL'
            CREATE TABLE generator_results (
                id TEXT PRIMARY KEY,
                generatorRequest TEXT NOT NULL,
                encodedImage TEXT NOT NULL,
                revisedPrompt TEXT NOT NULL,
                mimeType TEXT NOT NULL,
                image TEXT
            );
        SQL);
    }
}
