<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;
use Doctrine\DBAL\Connection;
use Override;

final class GeneratorRequestProvider extends DefaultSchemaProvider
{
    #[Override]
    public function getPriority(): int
    {
        return 10;
    }

    #[Override]
    public function createSchema(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS generator_requests (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                "userInput" TEXT NOT NULL,
                prompt TEXT
            );
        SQL);
    }
}
