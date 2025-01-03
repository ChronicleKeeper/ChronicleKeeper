<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class GeneratorRequestProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
        $platform->query(<<<'SQL'
            CREATE TABLE generator_requests (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                userInput TEXT NOT NULL,
                prompt TEXT
            );
        SQL);
    }
}
