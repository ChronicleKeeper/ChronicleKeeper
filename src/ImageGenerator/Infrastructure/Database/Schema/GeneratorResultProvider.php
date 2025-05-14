<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;
use Doctrine\DBAL\Connection;
use Override;

final class GeneratorResultProvider extends DefaultSchemaProvider
{
    #[Override]
    public function createSchema(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS generator_results (
                id TEXT PRIMARY KEY,
                "generatorRequest" TEXT NOT NULL,
                "encodedImage" TEXT NOT NULL,
                "revisedPrompt" TEXT NOT NULL,
                "mimeType" TEXT NOT NULL,
                image TEXT
            );
        SQL);
    }
}
